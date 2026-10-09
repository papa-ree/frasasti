<?php

use Bale\Frasasti\FrasastiServiceProvider;
use Bale\Frasasti\Livewire\Pages\Guest\Aduan\Index;
use Bale\Frasasti\Models\Aduan;
use Bale\Frasasti\Models\AduanCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Livewire\Livewire;
use Lunaweb\RecaptchaV3\Facades\RecaptchaV3;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->app->register(FrasastiServiceProvider::class);

    RecaptchaV3::shouldReceive('verify')
        ->andReturn(1.0)
        ->byDefault();
    RecaptchaV3::shouldReceive('initJs')
        ->andReturn('<script>/* mock recaptcha */</script>')
        ->byDefault();
    RecaptchaV3::shouldReceive('field')
        ->andReturn('<input type="hidden" name="g-recaptcha-response" value="test-token">')
        ->byDefault();
});

it('halaman bantuan bisa dirender', function () {
    Livewire::test(Index::class)
        ->assertStatus(200)
        ->assertSee('Formulir Aduan');
});

it('menampilkan halaman bantuan pada path /bantuan tanpa login', function () {
    AduanCategory::factory()->create(['name' => 'SSO']);

    $this->withoutVite()
        ->get('/bantuan')
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('Kirim ke WhatsApp')
        ->assertSee('SSO');
});

it('validasi form berfungsi untuk field kosong', function () {
    Livewire::test(Index::class)
        ->set('nama_lengkap', '')
        ->call('submit')
        ->assertHasErrors(['nama_lengkap' => 'required']);
});

it('menolak NIP yang tidak 18 digit', function () {
    Livewire::test(Index::class)
        ->set('nip', '12345')
        ->call('submit')
        ->assertHasErrors(['nip' => 'min']);
});

it('berhasil mengirim aduan, tersimpan dengan NIP & WA terenkripsi', function () {
    $category = AduanCategory::factory()->create(['name' => 'Aplikasi']);

    $response = Livewire::test(Index::class)
        ->set('nama_lengkap', '  User  Test  ')
        ->set('nip', '12345678 123456 7 812')
        ->set('wa_number', '081234567890')
        ->set('aduan_category_id', $category->id)
        ->set('deskripsi', 'Keluhan layanan TIK test')
        ->set('recaptchaToken', 'test-token')
        ->call('submit');

    $response
        ->assertHasNoErrors()
        ->assertRedirectContains('https://wa.me/'.config('frasasti.whatsapp.support_number').'?text=')
        ->assertRedirectContains(rawurlencode('*Aduan Bale*'))
        ->assertRedirectContains(rawurlencode('*Nama Lengkap*: User Test'))
        ->assertRedirectContains(rawurlencode('*NIP*: `123456781234567812`'));

    $aduan = Aduan::query()->first();

    expect($aduan)->not->toBeNull()
        ->and($aduan->ref_code)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i')
        ->and(Crypt::decryptString($aduan->getRawOriginal('nama_lengkap')))->toBe('User Test')
        ->and(Crypt::decryptString($aduan->getRawOriginal('nip')))->toBe('123456781234567812')
        ->and(Crypt::decryptString($aduan->getRawOriginal('wa_number')))->toBe('6281234567890')
        ->and($aduan->status)->toBe('pending');

    // Nomor WA pribadi tidak boleh ikut dikirim ke wa.me (privasi)
    expect($response->effects['redirect'] ?? '')
        ->not->toContain('081234567890')
        ->not->toContain('6281234567890');
});

it('menampilkan kategori yang tersedia di select', function () {
    $category = AduanCategory::factory()->create(['name' => 'IT Support']);

    Livewire::test(Index::class)
        ->assertSee('IT Support');
});

it('kategori Lainnya selalu di urutan terakhir', function () {
    AduanCategory::factory()->create(['name' => 'A']);
    AduanCategory::factory()->create(['name' => 'Lainnya']);
    AduanCategory::factory()->create(['name' => 'B']);

    $categories = Livewire::test(Index::class)->get('categories');
    expect($categories->last()->name)->toBe('Lainnya');
});

it('menampilkan error jika kategori tidak valid', function () {
    Livewire::test(Index::class)
        ->set('aduan_category_id', 'invalid-uuid')
        ->call('submit')
        ->assertHasErrors(['aduan_category_id' => 'exists']);
});

it('menolak aduan ketika skor reCAPTCHA rendah', function () {
    RecaptchaV3::shouldReceive('verify')->andReturn(0.1);

    $category = AduanCategory::factory()->create();

    Livewire::test(Index::class)
        ->set('nama_lengkap', 'User Test')
        ->set('nip', '12345678 123456 7 812')
        ->set('wa_number', '081234567890')
        ->set('aduan_category_id', $category->id)
        ->set('deskripsi', 'Keluhan layanan TIK test')
        ->set('recaptchaToken', 'test-token')
        ->call('submit')
        ->assertHasErrors('recaptchaToken');

    expect(Aduan::query()->count())->toBe(0);
});

it('membatasi jumlah pengiriman aduan per IP', function () {
    $category = AduanCategory::factory()->create();

    $makeComponent = fn () => Livewire::test(Index::class)
        ->set('nama_lengkap', 'User Test')
        ->set('nip', '12345678 123456 7 812')
        ->set('wa_number', '081234567890')
        ->set('aduan_category_id', $category->id)
        ->set('deskripsi', 'Keluhan layanan TIK test')
        ->set('recaptchaToken', 'test-token');

    // max_attempts = 5 (config frasasti.aduan.max_attempts)
    foreach (range(1, 5) as $ignored) {
        $makeComponent()->call('submit')->assertHasNoErrors();
    }

    // Percobaan ke-6 harus diblokir rate limiter
    $makeComponent()
        ->call('submit')
        ->assertHasErrors('nama_lengkap');

    expect(Aduan::query()->count())->toBe(5);
});

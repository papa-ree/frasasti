<?php

use Bale\Frasasti\Models\Aduan;
use Bale\Frasasti\Models\AduanCategory;
use Bale\Frasasti\Services\AduanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(AduanService::class);
    $this->category = AduanCategory::factory()->create();
});

it('sanitize menormalkan spasi berlebih pada nama lengkap', function () {
    $result = $this->service->sanitize(['nama_lengkap' => '  Budi   Santoso  ']);

    expect($result['nama_lengkap'])->toBe('Budi Santoso');
});

it('sanitize mempertahankan line break namun membuang baris kosong pada deskripsi', function () {
    $result = $this->service->sanitize(['deskripsi' => "Baris pertama\n\n  Baris kedua  "]);

    expect($result['deskripsi'])->toBe("Baris pertama\nBaris kedua");
});

it('sanitize mengembalikan NIP sebagai integer', function () {
    $result = $this->service->sanitize(['nip' => ' 12345678 123456 7 890 ']);

    expect($result['nip'])->toBe(123456781234567890);
});

it('normalizePhone mengubah awalan 0 menjadi 62', function () {
    $result = $this->service->sanitize(['wa_number' => '0812 3456 7890']);

    expect($result['wa_number'])->toBe('6281234567890');
});

it('normalizePhone mempertahankan nomor yang sudah berawalan 62', function () {
    $result = $this->service->sanitize(['wa_number' => '6281234567890']);

    expect($result['wa_number'])->toBe('6281234567890');
});

it('sanitize membuang karakter non-nomor dari nomor WhatsApp', function () {
    $result = $this->service->sanitize(['wa_number' => '0812-abc-3456']);

    expect($result['wa_number'])->toBe('628123456');
});

it('store membuat aduan berstatus pending dengan ref_code berformat UUID', function () {
    $aduan = $this->service->store([
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'wa_number' => '081234567890',
        'aduan_category_id' => $this->category->id,
        'deskripsi' => 'Keluhan layanan TIK',
    ]);

    expect($aduan)->toBeInstanceOf(Aduan::class)
        ->and((string) $aduan->ref_code)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i')
        ->and($aduan->status)->toBe('pending')
        ->and($aduan->nama_lengkap)->toBe('Budi Santoso')
        ->and($aduan->wa_number)->toBe('6281234567890')
        ->and($aduan->aduan_category_id)->toBe($this->category->id)
        ->and($aduan->ip_address)->not->toBeNull();
});

it('store menyimpan NIP dalam bentuk terenkripsi dan dapat dibaca kembali', function () {
    $aduan = $this->service->store([
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'wa_number' => '081234567890',
        'aduan_category_id' => $this->category->id,
        'deskripsi' => 'Keluhan layanan TIK',
    ]);

    $raw = DB::table('frasasti_aduans')->where('id', $aduan->id)->first();

    expect((string) $aduan->nip)->toBe('123456781234567890')
        ->and($raw->nip)->not->toBe('123456781234567890')
        ->and($raw->nama_lengkap)->not->toContain('Budi Santoso');
});

it('store menghasilkan ref_code yang berbeda tiap baris', function () {
    $payload = [
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'wa_number' => '081234567890',
        'aduan_category_id' => $this->category->id,
        'deskripsi' => 'Keluhan layanan TIK',
    ];

    $first = $this->service->store($payload);
    $second = $this->service->store($payload);

    expect((string) $first->ref_code)->not->toBe((string) $second->ref_code)
        ->and(Aduan::query()->count())->toBe(2);
});

it('buildWhatsAppLink membentuk pesan Aduan Bale lengkap ke nomor support', function () {
    $link = $this->service->buildWhatsAppLink([
        'ref_code' => 'abc-123',
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'aduan_category' => 'SSO',
        'deskripsi' => 'Tidak bisa login',
    ]);

    $decoded = rawurldecode($link);

    expect($link)->toStartWith('https://wa.me/'.config('frasasti.whatsapp.support_number').'?text=')
        ->and($decoded)->toContain('*Aduan Bale*')
        ->and($decoded)->toContain('*Kode Ref*: abc-123')
        ->and($decoded)->toContain('*Nama Lengkap*: Budi Santoso')
        ->and($decoded)->toContain('*NIP*: `123456781234567890`')
        ->and($decoded)->toContain('*Jenis Aduan*: SSO')
        ->and($decoded)->toContain('*Deskripsi*:')
        ->and($decoded)->toContain('Tidak bisa login');
});

it('buildWhatsAppLink tidak membocorkan nomor WhatsApp pengirim', function () {
    $link = $this->service->buildWhatsAppLink([
        'ref_code' => 'abc-123',
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'aduan_category' => 'SSO',
        'deskripsi' => 'Tidak bisa login',
        'wa_number' => '6281234567890',
    ]);

    expect(rawurldecode($link))
        ->not->toContain('6281234567890')
        ->not->toContain('*No. WhatsApp*');
});

it('buildWhatsAppLink memakai label kategori default bila kategori kosong', function () {
    $link = $this->service->buildWhatsAppLink([
        'ref_code' => 'abc-123',
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'deskripsi' => 'Tidak bisa login',
    ]);

    expect(rawurldecode($link))->toContain('*Jenis Aduan*: -');
});

it('buildWhatsAppLink dapat diarahkan ke nomor tujuan tertentu', function () {
    $link = $this->service->buildWhatsAppLink([
        'ref_code' => 'abc-123',
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'aduan_category' => 'SSO',
        'deskripsi' => 'Tidak bisa login',
    ], '6281999887777');

    expect($link)->toStartWith('https://wa.me/6281999887777?text=');
});

it('buildWhatsAppLink membuang karakter non-nomor pada nomor tujuan', function () {
    $link = $this->service->buildWhatsAppLink([
        'ref_code' => 'abc-123',
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'aduan_category' => 'SSO',
        'deskripsi' => 'Tidak bisa login',
    ], '+62 819-9988-777');

    expect($link)->toStartWith('https://wa.me/628199988777?text=');
});

it('buildWhatsAppLink menormalisasi awalan 0 pada nomor tujuan override', function () {
    $link = $this->service->buildWhatsAppLink([
        'ref_code' => 'abc-123',
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'aduan_category' => 'SSO',
        'deskripsi' => 'Tidak bisa login',
    ], '081999888777');

    expect($link)->toStartWith('https://wa.me/6281999888777?text=');
});

it('buildWhatsAppLink menormalisasi nomor default config bila berawalan 0', function () {
    config()->set('frasasti.whatsapp.support_number', '085126061182');

    $link = $this->service->buildWhatsAppLink([
        'ref_code' => 'abc-123',
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'aduan_category' => 'SSO',
        'deskripsi' => 'Tidak bisa login',
    ]);

    expect($link)->toStartWith('https://wa.me/6285126061182?text=');
});

it('buildWhatsAppLink memakai nomor default config saat target kosong', function () {
    $link = $this->service->buildWhatsAppLink([
        'ref_code' => 'abc-123',
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'aduan_category' => 'SSO',
        'deskripsi' => 'Tidak bisa login',
    ], '');

    expect($link)->toStartWith('https://wa.me/'.config('frasasti.whatsapp.support_number').'?text=');
});

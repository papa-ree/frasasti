<?php

use App\Models\User;
use Bale\Frasasti\FrasastiServiceProvider;
use Bale\Frasasti\Livewire\Pages\Landlord\AduanMasuk\Form as AduanMasukForm;
use Bale\Frasasti\Livewire\Pages\Landlord\AduanMasuk\Index as AduanMasukIndex;
use Bale\Frasasti\Models\Aduan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->app->register(FrasastiServiceProvider::class);

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'admin']);
    $this->user->assignRole($role);
    $this->actingAs($this->user);
});

it('menampilkan daftar aduan untuk admin dengan permission', function () {
    Permission::create(['name' => 'aduan.read']);
    $this->user->givePermissionTo('aduan.read');

    Aduan::factory()->count(3)->create();

    Livewire::test(AduanMasukIndex::class)
        ->assertStatus(200);
});

it('menolak akses jika admin tidak memiliki permission aduan.read', function () {
    Livewire::test(AduanMasukIndex::class)
        ->assertStatus(403);
});

it('dapat mengubah status aduan dari pending ke processed', function () {
    Permission::create(['name' => 'aduan.update']);
    $this->user->givePermissionTo('aduan.update');

    $aduan = Aduan::factory()->create(['status' => 'pending']);

    Livewire::test(AduanMasukForm::class, ['aduan' => $aduan])
        ->set('status', 'processed')
        ->call('updateStatus')
        ->assertHasNoErrors();

    expect($aduan->fresh()->status)->toBe('processed')
        ->and($aduan->fresh()->handled_at)->not->toBeNull()
        ->and($aduan->fresh()->handled_by)->toBe($this->user->uuid);
});

it('mencatat perubahan status aduan ke activity log', function () {
    Permission::create(['name' => 'aduan.update']);
    $this->user->givePermissionTo('aduan.update');

    $aduan = Aduan::factory()->create(['status' => 'pending']);

    // Event "created" dari factory sudah tercatat satu entri
    expect($aduan->activities()->count())->toBe(1);

    Livewire::test(AduanMasukForm::class, ['aduan' => $aduan])
        ->set('status', 'processed')
        ->call('updateStatus')
        ->assertHasNoErrors();

    // Pakai instance fresh: relasi activities() pada instance awal sudah ter-cache.
    $latest = Aduan::find($aduan->id)->activities()->orderByDesc('id')->first();

    // Spatie v5: perubahan atribut disimpan di `attribute_changes`,
    // sedangkan `properties` dipakai bale/core untuk metadata tenant/causer.
    $changes = $latest->attribute_changes->toArray();

    expect($latest->event)->toBe('updated')
        ->and($latest->subject_type)->toBe(Aduan::class)
        ->and($changes['attributes']['status'] ?? null)->toBe('processed')
        ->and($changes['attributes']['handled_by'] ?? null)->toBe($this->user->uuid)
        ->and($changes['old']['status'] ?? null)->toBe('pending');
});

it('tidak menyalin PII ke activity log saat aduan dibuat', function () {
    $aduan = Aduan::factory()->create([
        'nama_lengkap' => 'Budi Santoso',
        'nip' => '123456781234567890',
        'wa_number' => '6281234567890',
        'ip_address' => '10.0.0.7',
        'deskripsi' => 'Tidak bisa login',
    ]);

    $created = Aduan::find($aduan->id)->activities()->orderByDesc('id')->first();
    $attributes = $created->attribute_changes->toArray()['attributes'] ?? [];

    // PII tidak boleh ikut masuk tabel activity_log (tabel ini tidak terenkripsi).
    expect($created->event)->toBe('created')
        ->and($attributes)->not->toHaveKeys([
            'nama_lengkap',
            'nip',
            'wa_number',
            'ip_address',
        ]);

    // reopened serialized payload juga tidak boleh memuat PII.
    $raw = json_encode($created->attribute_changes);

    expect($raw)->not->toContain('Budi Santoso')
        ->and($raw)->not->toContain('123456781234567890')
        ->and($raw)->not->toContain('6281234567890')
        ->and($raw)->not->toContain('10.0.0.7');

    // Field operasional tetap tercatat.
    expect($attributes)->toHaveKey('deskripsi')
        ->and($attributes)->toHaveKey('ref_code')
        ->and($attributes)->toHaveKey('status');
});

it('menolak perubahan pada isi aduan setelah dibuat', function () {
    $aduan = Aduan::factory()->create([
        'nama_lengkap' => 'Budi Santoso',
        'deskripsi' => 'Tidak bisa login',
    ]);

    $originalName = $aduan->nama_lengkap;
    $originalDescription = $aduan->deskripsi;

    foreach ([
        'nama_lengkap' => 'Nama Baru',
        'nip' => '9999999999999999',
        'wa_number' => '628999999999',
        'deskripsi' => 'Keluhan direkayasa',
        'ref_code' => '11111111-2222-3333-4444-555555555555',
    ] as $attribute => $newValue) {
        expect(fn () => Aduan::find($aduan->id)->update([$attribute => $newValue]))
            ->toThrow(LogicException::class, $attribute);
    }

    // Nilai di database tetap sama seperti saat dibuat.
    $fresh = Aduan::find($aduan->id);

    expect($fresh->nama_lengkap)->toBe($originalName)
        ->and($fresh->deskripsi)->toBe($originalDescription)
        ->and((string) $fresh->ref_code)->toBe((string) $aduan->ref_code);
});

it('tetap mengizinkan perubahan status dan field penanganan', function () {
    $aduan = Aduan::factory()->create(['status' => 'pending']);

    $aduan->update([
        'status' => 'processed',
        'handled_at' => now(),
    ]);

    $fresh = Aduan::find($aduan->id);

    expect($fresh->status)->toBe('processed')
        ->and($fresh->handled_at)->not->toBeNull();
});

it('menampilkan hanya aduan pending saat filter pending aktif', function () {
    Permission::create(['name' => 'aduan.read']);
    $this->user->givePermissionTo('aduan.read');

    $pending = Aduan::factory()->create(['status' => 'pending']);
    $done = Aduan::factory()->create(['status' => 'done']);

    // Filter dikelola data-table lewat activeFilters (slot "filters")
    Livewire::test('core-shared-components::data-table', [
        'model' => Aduan::class,
        'rowView' => 'frasasti::livewire.pages.landlord.aduan-masuk.section.aduan-masuk-row',
        'with' => ['category'],
        'columns' => [],
        'activeFilters' => ['status' => 'pending'],
    ])
        ->assertSee($pending->ref_code)
        ->assertSee('Menunggu')
        ->assertDontSee($done->ref_code);
});

it('menolak edit aduan tanpa permission aduan.update', function () {
    $aduan = Aduan::factory()->create(['status' => 'pending']);

    $this->get(route('frasasti.aduan-masuk.edit', $aduan))
        ->assertForbidden();
});

it('menampilkan pesan error saat update ke status tidak valid', function () {
    Permission::create(['name' => 'aduan.update']);
    $this->user->givePermissionTo('aduan.update');

    $aduan = Aduan::factory()->create(['status' => 'pending']);

    Livewire::test(AduanMasukForm::class, ['aduan' => $aduan])
        ->set('status', 'invalid-status')
        ->call('updateStatus')
        ->assertHasErrors(['status' => 'in']);
});

it('merender halaman Aduan Masuk memakai layout rakaca', function () {
    Permission::create(['name' => 'aduan.read']);
    Permission::create(['name' => 'dashboard']);
    $this->user->givePermissionTo(['aduan.read', 'dashboard']);

    Aduan::factory()->create(['status' => 'pending']);

    // 'preloader' hanya ada di rakaca::layouts.app
    $this->withoutVite()
        ->get(route('frasasti.aduan-masuk.index'))
        ->assertOk()
        ->assertSee('preloader')
        ->assertSee('Aduan Masuk');
});

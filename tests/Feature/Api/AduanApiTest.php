<?php

use Bale\Api\Services\TokenManager;
use Bale\Frasasti\Models\Aduan;
use Bale\Frasasti\Models\AduanCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tokens = app(TokenManager::class);
});

it('token dengan scope frasasti.aduan.read dapat melihat daftar aduan', function () {
    Aduan::factory()->count(2)->create();

    $issued = $this->tokens->issue('Client', ['frasasti.aduan.read']);

    $this->getJson('/api/v1/frasasti/aduans', ['Authorization' => 'Bearer '.$issued['plain']])
        ->assertOk()
        ->assertJsonStructure(['data', 'meta'])
        ->assertJsonCount(2, 'data');
});

it('token tanpa scope frasasti.aduan.read ditolak 403', function () {
    Aduan::factory()->create();

    $issued = $this->tokens->issue('Client', ['loker.read']);

    $this->getJson('/api/v1/frasasti/aduans', ['Authorization' => 'Bearer '.$issued['plain']])
        ->assertForbidden();
});

it('permintaan tanpa token ditolak 401', function () {
    $this->getJson('/api/v1/frasasti/aduans')
        ->assertUnauthorized();
});

it('daftar aduan mengembalikan field ter-dekripsi beserta relasi', function () {
    $category = AduanCategory::factory()->create(['name' => 'SSO']);
    Aduan::factory()->create([
        'aduan_category_id' => $category->id,
        'nama_lengkap' => 'Budi Santoso',
        'status' => 'pending',
    ]);

    $issued = $this->tokens->issue('Client', ['frasasti.aduan.read']);

    $this->getJson('/api/v1/frasasti/aduans', ['Authorization' => 'Bearer '.$issued['plain']])
        ->assertOk()
        ->assertJsonPath('data.0.nama_lengkap', 'Budi Santoso')
        ->assertJsonPath('data.0.status', 'pending')
        ->assertJsonPath('data.0.status_label', 'Menunggu')
        ->assertJsonPath('data.0.category.name', 'SSO');
});

it('filter status hanya mengembalikan aduan yang sesuai', function () {
    Aduan::factory()->create(['status' => 'pending']);
    Aduan::factory()->create(['status' => 'done']);

    $issued = $this->tokens->issue('Client', ['frasasti.aduan.read']);

    $this->getJson('/api/v1/frasasti/aduans?status=pending', ['Authorization' => 'Bearer '.$issued['plain']])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'pending');
});

it('pencarian q mencocokkan ref_code', function () {
    $target = Aduan::factory()->create();
    Aduan::factory()->create();

    $issued = $this->tokens->issue('Client', ['frasasti.aduan.read']);

    $this->getJson('/api/v1/frasasti/aduans?q='.$target->ref_code, ['Authorization' => 'Bearer '.$issued['plain']])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.ref_code', (string) $target->ref_code);
});

it('detail aduan dapat diambil dan 404 untuk uuid yang tidak dikenal', function () {
    $aduan = Aduan::factory()->create();

    $issued = $this->tokens->issue('Client', ['frasasti.aduan.read']);

    $this->getJson('/api/v1/frasasti/aduans/'.$aduan->id, ['Authorization' => 'Bearer '.$issued['plain']])
        ->assertOk()
        ->assertJsonStructure(['data' => ['id', 'ref_code', 'nama_lengkap', 'nip', 'wa_number', 'status', 'status_label', 'category', 'handled_by']])
        ->assertJsonPath('data.id', (string) $aduan->id);

    $this->getJson('/api/v1/frasasti/aduans/00000000-0000-0000-0000-000000000000', ['Authorization' => 'Bearer '.$issued['plain']])
        ->assertNotFound();
});

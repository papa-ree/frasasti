<?php

use Bale\Frasasti\Models\Aduan;
use Bale\Frasasti\Models\AduanCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

/**
 * Tabel legacy `rakaca_aduans` masih ada di database aplikasi (dibiarkan
 * sengaja saat teardown), sehingga test memakainya apa adanya.
 */
function legacyCategoryId(): string
{
    // Primary key tabel legacy bertipe UUID, jadi id harus dibuat manual
    // (insertGetId mengembalikan rowid, bukan UUID).
    $id = (string) Str::uuid();

    DB::table('rakaca_aduan_categories')->insert([
        'id' => $id,
        'name' => 'Kategori Legacy '.Str::random(6),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

function insertLegacyAduan(array $overrides = []): string
{
    return DB::table('rakaca_aduans')->insertGetId(array_merge([
        'id' => (string) Str::uuid(),
        'ref_code' => (string) Str::uuid(),
        // Cast `encrypted` pada model Rakaca memakai encrypt($value, false)
        // (tanpa serialize), jadi fixture harus mengikuti cara yang sama.
        'nama_lengkap' => Crypt::encrypt('Siti Aminah', false),
        'nip' => Crypt::encrypt('9876543210987654', false),
        'wa_number' => Crypt::encrypt('628999888777', false),
        'aduan_category_id' => legacyCategoryId(),
        'deskripsi' => 'Keluhan lama',
        'status' => 'pending',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

describe('frasasti:install', function () {
    it('menjalankan command install tanpa error', function () {
        $this->artisan('frasasti:install')->assertSuccessful();
    });

    it('menyiapkan permission aduan.read dan aduan.update secara idempotent', function () {
        $this->artisan('frasasti:install')->assertSuccessful();
        $this->artisan('frasasti:install')->assertSuccessful();

        expect(Permission::where('name', 'aduan.read')->count())->toBe(1)
            ->and(Permission::where('name', 'aduan.update')->count())->toBe(1);
    });

    it('menyiapkan kategori aduan tanpa duplikat pada dijalankan berulang', function () {
        $this->artisan('frasasti:install')->assertSuccessful();
        $first = AduanCategory::query()->count();

        $this->artisan('frasasti:install')->assertSuccessful();

        expect($first)->toBeGreaterThan(0)
            ->and(AduanCategory::query()->count())->toBe($first);
    });
});

describe('frasasti:import-legacy-aduan', function () {
    it('tidak menulis apa pun pada mode dry-run', function () {
        insertLegacyAduan();

        $this->artisan('frasasti:import-legacy-aduan', ['--dry-run' => true])->assertSuccessful();

        expect(Aduan::query()->count())->toBe(0);
    });

    it('berhasil tanpa error ketika tabel legacy tidak ada', function () {
        Schema::dropIfExists('rakaca_aduans');

        $this->artisan('frasasti:import-legacy-aduan')->assertSuccessful();

        expect(Aduan::query()->count())->toBe(0);
    });

    it('mengimpor baris legacy dan mendekripsi field terenkripsi', function () {
        $refCode = (string) Str::uuid();
        insertLegacyAduan(['ref_code' => $refCode]);

        $this->artisan('frasasti:import-legacy-aduan')->assertSuccessful();

        $aduan = Aduan::query()->first();

        expect(Aduan::query()->count())->toBe(1)
            ->and($aduan->ref_code)->toBe($refCode)
            ->and($aduan->nama_lengkap)->toBe('Siti Aminah')
            ->and($aduan->nip)->toBe('9876543210987654')
            ->and($aduan->wa_number)->toBe('628999888777')
            ->and($aduan->deskripsi)->toBe('Keluhan lama')
            ->and($aduan->status)->toBe('pending');
    });

    it('melewati baris dengan ref_code yang sudah pernah diimpor', function () {
        $refCode = (string) Str::uuid();
        $legacyId = insertLegacyAduan(['ref_code' => $refCode]);

        $this->artisan('frasasti:import-legacy-aduan')->assertSuccessful();
        expect(Aduan::query()->count())->toBe(1);

        DB::table('rakaca_aduans')->where('id', $legacyId)->update(['deskripsi' => 'Diubah setelah impor']);

        $this->artisan('frasasti:import-legacy-aduan')->assertSuccessful();

        expect(Aduan::query()->count())->toBe(1)
            ->and(Aduan::query()->first()->deskripsi)->toBe('Keluhan lama');
    });

    it('mengimpor seluruh baris legacy yang belum ada', function () {
        insertLegacyAduan(['ref_code' => (string) Str::uuid()]);
        insertLegacyAduan(['ref_code' => (string) Str::uuid()]);

        $this->artisan('frasasti:import-legacy-aduan')->assertSuccessful();

        expect(Aduan::query()->count())->toBe(2);
    });
});

describe('frasasti:publish-migration', function () {
    it('mempertahankan dua migration frasasti dan tidak menggandakan file', function () {
        $this->artisan('frasasti:publish-migration')->assertSuccessful();

        $published = glob(database_path('migrations/*_create_frasasti_*.php'));

        expect($published)->toHaveCount(2);
    });
});

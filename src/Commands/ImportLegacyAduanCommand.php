<?php

namespace Bale\Frasasti\Commands;

use Bale\Frasasti\Models\Aduan;
use Bale\Frasasti\Models\AduanCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Migrasi data aduan legacy dari tabel rakaca_* ke tabel frasasti_*.
 *
 * Sengaja TIDAK memakai model Paparee\Rakaca\Models\Aduan supaya command ini
 * tetap bisa dijalankan berulang kali, bahkan setelah package rakaca melepas
 * kepemilikan aduan (Phase 6). Pembacaan dilakukan lewat query builder dan
 * dekripsi manual memakai APP_KEY yang sama.
 *
 * Aman diulang: baris yang ref_code-nya sudah ada di tabel baru dilewati.
 */
class ImportLegacyAduanCommand extends Command
{
    protected $signature = 'frasasti:import-legacy-aduan
                            {--dry-run : Tampilkan rencana import tanpa menulis data}';

    protected $description = 'Import data aduan legacy (rakaca_aduans) ke tabel frasasti_aduans';

    public function handle(): int
    {
        if (! Schema::hasTable('rakaca_aduans')) {
            $this->warn('Tabel legacy rakaca_aduans tidak ada — tidak ada yang diimpor.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $rows = DB::table('rakaca_aduans')->orderBy('created_at')->get();

        if ($rows->isEmpty()) {
            $this->info('Tabel legacy kosong — tidak ada data untuk diimpor.');

            return self::SUCCESS;
        }

        // Kunci kategori lama agar urutan penamaan tidak bergantung pada UUID.
        $legacyCategories = $this->legacyCategoryNames();

        $this->info(sprintf('Menemukan %d baris legacy di rakaca_aduans.', $rows->count()));

        if ($dryRun) {
            $this->line('  Mode dry-run: tidak ada data yang ditulis.');
        }

        $imported = $skipped = $failed = 0;

        foreach ($rows as $row) {
            if (Aduan::query()->where('ref_code', $row->ref_code)->exists()) {
                $skipped++;
                $this->line(sprintf('  - Lewati (sudah ada): %s', $row->ref_code));

                continue;
            }

            $payload = $this->transform($row, $legacyCategories);

            if ($payload === null) {
                $failed++;
                $this->error(sprintf('  - Gagal dekripsi baris: %s', $row->ref_code));

                continue;
            }

            if ($dryRun) {
                $imported++;
                $this->line(sprintf('  - Akan diimpor: %s (%s)', $row->ref_code, $payload['aduan_category_id']));

                continue;
            }

            try {
                Aduan::query()->create($payload);
                $imported++;
                $this->line(sprintf('  + Berhasil: %s', $row->ref_code));
            } catch (Throwable $e) {
                $failed++;
                $this->error(sprintf('  - Gagal menyimpan %s: %s', $row->ref_code, $e->getMessage()));
            }
        }

        $this->newLine();
        $this->info(sprintf('Ringkasan — diimpor: %d, dilewati: %d, gagal: %d.', $imported, $skipped, $failed));

        if (! $dryRun && $imported > 0) {
            $this->info(sprintf('Total data sekarang di frasasti_aduans: %d', Aduan::query()->count()));
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Nama kategori legacy (rakaca_aduan_categories) berdasarkan uuid.
     * Bila tabel/kategori hilang, fallback ke "Lainnya".
     *
     * @return array<string, string>
     */
    protected function legacyCategoryNames(): array
    {
        if (! Schema::hasTable('rakaca_aduan_categories')) {
            return [];
        }

        return DB::table('rakaca_aduan_categories')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * Ubah satu baris legacy menjadi payload untuk tabel baru.
     *
     * @param  array<string, string>  $legacyCategories
     * @return array<string, mixed>|null
     */
    protected function transform(object $row, array $legacyCategories): ?array
    {
        try {
            $categoryName = $legacyCategories[$row->aduan_category_id] ?? 'Lainnya';

            $category = AduanCategory::firstOrCreate(
                ['name' => $categoryName],
                ['id' => (string) Str::uuid()]
            );

            return [
                'ref_code' => $row->ref_code,
                'nama_lengkap' => Crypt::decryptString($row->nama_lengkap),
                'nip' => Crypt::decryptString($row->nip),
                'wa_number' => Crypt::decryptString($row->wa_number),
                'aduan_category_id' => $category->id,
                'deskripsi' => $row->deskripsi,
                'status' => $row->status ?: 'pending',
                'ip_address' => $row->ip_address,
                'handled_at' => null,
                'handled_by' => null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}

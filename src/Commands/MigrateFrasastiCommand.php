<?php

namespace Bale\Frasasti\Commands;

use Illuminate\Console\Command;

class MigrateFrasastiCommand extends Command
{
    protected $signature = 'frasasti:migrate {--force : Jalankan migrate pada koneksi landlord}';

    protected $description = 'Publish lalu jalankan migration bale/frasasti pada koneksi landlord (default)';

    /**
     * Catatan: berbeda dengan `loker:migrate` yang tenant-scoped, tabel
     * `frasasti_aduans` berada pada koneksi landlord/default karena menu
     * Aduan Masuk terdaftar sebagai `type: landlord`.
     */
    public function handle(): int
    {
        $this->info('Publishing Frasasti migrations...');
        $this->call('frasasti:publish-migration');

        $arguments = ['--path' => 'database/migrations'];

        // Sengaja tanpa --force secara default agar production tidak bisa
        // salah migrate tanpa-flag. Gunakan --force bila memang disengaja.
        if ($this->option('force')) {
            $arguments['--force'] = true;
        }

        $this->info('Running migrations on the default (landlord) connection...');

        return $this->call('migrate', $arguments);
    }
}

<?php

namespace Bale\Frasasti\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PublishMigrationCommand extends Command
{
    protected $signature = 'frasasti:publish-migration {--all : Publish semua migrationstub yang tersedia}';

    protected $description = 'Publish migration stub bale/frasasti ke database/migrations aplikasi';

    public function handle(): int
    {
        $sourcePath = __DIR__.'/../../database/migrations/';

        if (! File::isDirectory($sourcePath)) {
            $this->error("Source directory not found: {$sourcePath}");

            return self::FAILURE;
        }

        $stubs = collect(File::files($sourcePath))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.php.stub'));

        if ($stubs->isEmpty()) {
            $this->warn('Tidak ada migration stub yang perlu dipublish.');

            return self::SUCCESS;
        }

        $timestamp = date('Y_m_d_His');
        $published = 0;
        $skipped = 0;

        foreach ($stubs as $stub) {
            $name = Str::before($stub->getFilename(), '.php.stub');
            $target = database_path("migrations/{$timestamp}_{$name}.php");

            // Sudah pernah dipublish dengan nama yang sama? Lewati.
            $existing = collect(File::files(database_path('migrations')))
                ->first(fn ($file) => Str::contains($file->getFilename(), $name));

            if ($existing) {
                $skipped++;
                $this->line("  - Lewati (sudah ada): {$existing->getFilename()}");

                continue;
            }

            File::copy($stub->getPathname(), $target);
            $published++;
            $this->line("  + Publish: {$name}.php");
        }

        $this->newLine();
        $this->info(sprintf('Selesai. Dipublish: %d, dilewati: %d.', $published, $skipped));

        if ($published > 0) {
            $this->comment('Jalankan `php artisan migrate` untuk menerapkan migration baru.');
        }

        return self::SUCCESS;
    }
}

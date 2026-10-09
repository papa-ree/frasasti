<?php

namespace Bale\Frasasti\Commands;

use Illuminate\Console\Command;

class PublishConfigCommand extends Command
{
    protected $signature = 'frasasti:publish-config';

    protected $description = 'Publish konfigurasi bale/frasasti ke aplikasi';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'frasasti:config',
            '--force' => true,
        ]);

        $this->info('Config berhasil dipublish ke config/frasasti.php.');

        return self::SUCCESS;
    }
}

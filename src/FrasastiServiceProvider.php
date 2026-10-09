<?php

namespace Bale\Frasasti;

use Bale\Api\Http\Controllers\Api\BaseApiController;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;

class FrasastiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/frasasti.php', 'frasasti');

        $this->registerCommands();
    }

    protected function registerCommands(): void
    {
        $commands = [
            'command.frasasti:install' => Commands\InstallFrasastiCommand::class,
            'command.frasasti:import-legacy-aduan' => Commands\ImportLegacyAduanCommand::class,
            'command.frasasti:publish-migration' => Commands\PublishMigrationCommand::class,
            'command.frasasti:migrate' => Commands\MigrateFrasastiCommand::class,
            'command.frasasti:publish-config' => Commands\PublishConfigCommand::class,
        ];

        foreach ($commands as $key => $class) {
            $this->app->bind($key, $class);
        }

        $this->commands(array_keys($commands));
    }

    /**
     * Boot the service provider.
     *
     * Dipanggil setelah semua service diregistrasi.
     * Memuat view, migration, konfigurasi, dan Livewire component.
     */
    public function boot(): void
    {
        $this->registerApiScopes();

        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

            if (class_exists(BaseApiController::class)) {
                $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
            }
        });

        $this->registerViews();
        $this->loadMigrations();
        $this->offerPublishing();
        $this->registerLivewireComponents();
    }

    /**
     * Daftarkan scope API milik frasasti ke registry bale/api.
     */
    protected function registerApiScopes(): void
    {
        if (! function_exists('registerApiScopes')) {
            return;
        }

        registerApiScopes('frasasti', [
            'frasasti.aduan.read' => 'Membaca daftar dan detail aduan masuk (data pengirim ter-dekripsi).',
        ]);
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            'frasasti'
        );
    }

    /**
     * Load migration langsung dari package.
     *
     * Dengan ini, user bisa langsung menjalankan migration
     * tanpa harus publish file ke aplikasi utama.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Publish file agar bisa diubah oleh user.
     */
    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/frasasti.php' => config_path('frasasti.php'),
        ], 'frasasti:config');

        $this->publishes($this->getMigrations(), 'frasasti:migrations');

        $this->publishes([
            __DIR__.'/../src/Database/Seeders/AduanCategorySeeder.php' => database_path('seeders/AduanCategorySeeder.php'),
        ], 'frasasti:seeders');
    }

    /**
     * Mengambil semua file migration dari direktori package.
     */
    protected function getMigrations(): array
    {
        $migrations = [];
        $sourcePath = __DIR__.'/../database/migrations/';

        if (! is_dir($sourcePath)) {
            return $migrations;
        }

        foreach (glob($sourcePath.'*.{php,stub}', GLOB_BRACE) as $file) {
            $filename = basename($file);

            $targetFile = $this->getMigrationFileName($filename);

            $migrations[$file] = $targetFile;
        }

        return $migrations;
    }

    /**
     * Membuat nama file migration yang sesuai dengan timestamp laravel.
     */
    protected function getMigrationFileName(string $filename): string
    {
        $timestamp = date('Y_m_d_His');
        $migrationName = str_replace('.php.stub', '.php', $filename);

        return database_path('migrations/'.$timestamp.'_'.$migrationName);
    }

    /**
     * Registrasi semua Livewire Component secara otomatis dari folder src/Livewire.
     *
     * Contoh mapping:
     *   src/Livewire/Overview.php                          => <livewire:frasasti.overview />
     *   src/Livewire/Pages/Guest/Aduan/Index.php           => <livewire:frasasti.pages.guest.aduan.index />
     *   src/Livewire/Pages/Landlord/AduanMasuk/Index.php   => <livewire:frasasti.pages.landlord.aduan-masuk.index />
     */
    protected function registerLivewireComponents(): void
    {
        $namespace = 'Bale\\Frasasti\\Livewire';
        $basePath = __DIR__.'/Livewire';

        if (! is_dir($basePath)) {
            return;
        }

        $finder = new Finder;
        $finder->files()->in($basePath)->name('*.php');

        foreach ($finder as $file) {
            $relativePathname = $file->getRelativePathname();
            $nsPath = str_replace(['/', '\\'], '\\', $relativePathname);

            $class = $namespace.'\\'.Str::beforeLast($nsPath, '.php');

            if (! class_exists($class)) {
                continue;
            }

            if (! is_subclass_of($class, LivewireComponent::class)) {
                continue;
            }

            $withoutExt = Str::replaceLast('.php', '', $relativePathname);
            $segments = preg_split('#[\\/\\\\]#', $withoutExt);
            $kebab = array_map(fn ($s) => Str::kebab($s), $segments);

            $alias = 'frasasti.'.implode('.', $kebab);

            Livewire::component($alias, $class);
        }
    }
}

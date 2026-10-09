<?php

namespace Bale\Frasasti\Commands;

use Bale\Frasasti\Database\Seeders\AduanCategorySeeder;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InstallFrasastiCommand extends Command
{
    protected $signature = 'frasasti:install';

    protected $description = 'Install Frasasti: Seed aduan categories and permissions';

    public function handle(): int
    {
        $this->info('Starting Frasasti installation...');

        $this->call(AduanCategorySeeder::class);
        $this->info('Aduan categories seeded.');

        $this->seedPermissions();

        $this->info('Frasasti installation completed successfully!');

        return self::SUCCESS;
    }

    protected function seedPermissions(): void
    {
        $this->info('Seeding Frasasti permissions...');

        $permissions = [
            'aduan.read',
            'aduan.update',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['guard_name' => 'web']);
        }

        $this->info('Permissions seeded and updated.');

        $rootRole = Role::where('name', 'root')->first();
        if ($rootRole) {
            $this->info('Syncing Frasasti permissions to root role...');
            $rootRole->givePermissionTo($permissions);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->info('Permissions synced and cache cleared for root role.');
        }
    }
}

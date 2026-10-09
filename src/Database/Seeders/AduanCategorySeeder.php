<?php

namespace Bale\Frasasti\Database\Seeders;

use Bale\Frasasti\Models\AduanCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AduanCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['SSO', 'Infrastruktur', 'Aplikasi', 'Lainnya'] as $name) {
            AduanCategory::firstOrCreate(
                ['name' => $name],
                ['id' => Str::uuid()]
            );
        }
    }
}

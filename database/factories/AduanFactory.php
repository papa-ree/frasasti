<?php

namespace Bale\Frasasti\Database\Factories;

use Bale\Frasasti\Models\Aduan;
use Bale\Frasasti\Models\AduanCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AduanFactory extends Factory
{
    protected $model = Aduan::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'ref_code' => Str::uuid(),
            'nama_lengkap' => $this->faker->name(),
            'nip' => $this->faker->numerify('##################'),
            'wa_number' => $this->faker->phoneNumber(),
            'aduan_category_id' => AduanCategory::factory(),
            'deskripsi' => $this->faker->paragraph(),
            'status' => 'pending',
            'ip_address' => $this->faker->ipv4(),
        ];
    }
}

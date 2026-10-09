<?php

namespace Bale\Frasasti\Database\Factories;

use Bale\Frasasti\Models\AduanCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class AduanCategoryFactory extends Factory
{
    protected $model = AduanCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Mock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Mock> */
class MockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payload' => json_encode([
                'id' => $this->faker->randomNumber(),
                'name' => $this->faker->name(),
                'role' => $this->faker->randomElement(['admin', 'user']),
            ]),
            'slug' => Str::lower(Str::random(8)),
            'status_code' => 200,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Paste;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Paste> */
class PasteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(),
            'expires_at' => null,
            'burn_after_read' => true,
            'hash' => Str::random(32),
        ];
    }

    /**
     * Paste qui expire dans le passé (à purger).
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subHour(),
            'burn_after_read' => false,
        ]);
    }

    /**
     * Paste encore valide.
     */
    public function expiring(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->addHour(),
            'burn_after_read' => false,
        ]);
    }
}

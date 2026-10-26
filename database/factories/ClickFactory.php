<?php

namespace Database\Factories;

use App\Models\Click;
use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Click> */
class ClickFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url_id' => Url::factory(),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'country_code' => $this->faker->randomElement(['FR', 'US', 'DE', 'MA']),
            'referer' => $this->faker->randomElement([
                'https://google.com',
                'https://x.com',
                null,
            ]),
            'user_agent' => 'Mozilla/5.0',
            'created_at' => $this->faker->dateTimeBetween('-29 days'),
        ];
    }

    /**
     * Clic récent (pour les tuiles 30 jours).
     */
    public function recent(): static
    {
        return $this->state(fn () => [
            'created_at' => now()->subDays($this->faker->numberBetween(0, 6)),
        ]);
    }
}

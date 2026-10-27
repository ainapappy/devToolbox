<?php

namespace Database\Factories;

use App\Models\KnownDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnownDevice>
 */
class KnownDeviceFactory extends Factory
{
    protected $model = KnownDevice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fingerprint' => $this->faker->sha256(),
            'device' => 'Chrome sur Windows 11',
            'first_seen_at' => now(),
        ];
    }
}

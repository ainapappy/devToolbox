<?php

namespace Database\Factories;

use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Url> */
class UrlFactory extends Factory
{
    public function definition(): array
    {
        return [
            'target_url' => $this->faker->url(),
            'code' => Str::lower(Str::random(6)),
        ];
    }
}

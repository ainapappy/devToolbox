<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Document> */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => "# {$this->faker->sentence()}\n\n{$this->faker->paragraph()}",
            'slug' => Str::lower(Str::random(8)),
        ];
    }
}

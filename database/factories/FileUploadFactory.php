<?php

namespace Database\Factories;

use App\Models\FileUpload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<FileUpload> */
class FileUploadFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->word().'.txt';

        return [
            'uuid' => (string) Str::uuid(),
            'original_name' => $name,
            'path' => 'uploads/'.Str::random(40),
            'mime_type' => 'text/plain',
            'size' => 1024,
            'downloads' => 0,
            'max_downloads' => null,
            'password_hash' => null,
            'expires_at' => null,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Lien expiré (comme PasteFactory::expired()).
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subHour(),
        ]);
    }

    /**
     * Quota de téléchargements épuisé.
     */
    public function exhausted(): static
    {
        return $this->state(fn () => [
            'max_downloads' => 1,
            'downloads' => 1,
        ]);
    }

    /**
     * Fichier protégé par mot de passe (« secret » par défaut).
     */
    public function locked(?string $password = 'secret'): static
    {
        return $this->state(fn () => [
            'password_hash' => Hash::make($password),
        ]);
    }
}

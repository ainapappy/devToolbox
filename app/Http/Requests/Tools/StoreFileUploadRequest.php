<?php

namespace App\Http\Requests\Tools;

use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFileUploadRequest extends FormRequest
{
    /**
     * Durées de validité proposées par l'interface (pills, comme Secure
     * Paste). Absence d'option = lien sans expiration.
     */
    public const DURATIONS = ['1h', '24h', '7j'];

    /**
     * Choix de quota de téléchargements proposés par l'interface (pills).
     * Toute valeur entière ≥ 1 est acceptée par le serveur ; absent ou
     * null = illimité.
     */
    public const DOWNLOAD_OPTIONS = [1, 3, 5];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Règles pilotées par config/devtoolbox.php : aucune limite codée en dur.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'max:'.$this->maxFiles()],
            'files.*' => [
                'file',
                'max:'.$this->maxSizeKb(),
                'mimes:'.$this->allowedMimes(),
            ],
            'max_downloads' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'expiration' => ['nullable', Rule::in(self::DURATIONS)],
            'password' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Taille maximale par fichier (ko).
     */
    private function maxSizeKb(): int
    {
        return (int) config('devtoolbox.uploads.max_size_kb');
    }

    /**
     * Nombre maximal de fichiers par upload.
     */
    private function maxFiles(): int
    {
        return (int) config('devtoolbox.uploads.max_files');
    }

    /**
     * Extensions autorisées, au format de la règle `mimes`.
     */
    private function allowedMimes(): string
    {
        return implode(',', (array) config('devtoolbox.uploads.allowed_mimes'));
    }

    /**
     * Durée de vie du lien selon l'option choisie (null = sans expiration).
     */
    public function expiresAt(): ?CarbonInterface
    {
        return match ($this->string('expiration')->toString()) {
            '1h' => now()->addHour(),
            '24h' => now()->addDay(),
            '7j' => now()->addDays(7),
            default => null,
        };
    }

    /**
     * Quota de téléchargements choisi (null = illimité).
     */
    public function maxDownloads(): ?int
    {
        return $this->filled('max_downloads') ? $this->integer('max_downloads') : null;
    }

    /**
     * Mot de passe en clair à hasher (null si absent ou vide).
     */
    public function password(): ?string
    {
        $password = $this->string('password')->toString();

        return $password === '' ? null : $password;
    }
}

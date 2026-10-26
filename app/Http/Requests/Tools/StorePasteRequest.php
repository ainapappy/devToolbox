<?php

namespace App\Http\Requests\Tools;

use Carbon\CarbonInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePasteRequest extends FormRequest
{
    /**
     * Options d'expiration proposées par l'interface (pills).
     */
    public const EXPIRATIONS = ['burn', '1h', '24h'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:50000'],
            'expiration' => [Rule::in(self::EXPIRATIONS)],
        ];
    }

    /**
     * Durée de vie correspondant à l'option choisie.
     */
    public function expiresAt(): ?CarbonInterface
    {
        return match ($this->string('expiration')->toString()) {
            '1h' => now()->addHour(),
            '24h' => now()->addDay(),
            default => null,
        };
    }

    /**
     * Destruction après la première lecture ?
     */
    public function burnAfterRead(): bool
    {
        return $this->string('expiration')->toString() === 'burn';
    }
}

<?php

namespace App\Http\Requests\Tools;

use Illuminate\Foundation\Http\FormRequest;

class StoreMockRequest extends FormRequest
{
    /**
     * Taille maximale du payload (caractères).
     */
    public const MAX_PAYLOAD = 100000;

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
            'payload' => ['required', 'json', 'max:'.self::MAX_PAYLOAD],
            'status_code' => ['required', 'integer', 'between:200,599'],
        ];
    }

    /**
     * Status code choisi pour l'endpoint.
     */
    public function statusCode(): int
    {
        return (int) $this->integer('status_code');
    }
}

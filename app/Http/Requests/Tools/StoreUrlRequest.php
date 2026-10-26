<?php

namespace App\Http\Requests\Tools;

use Illuminate\Foundation\Http\FormRequest;

class StoreUrlRequest extends FormRequest
{
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
            'target_url' => ['required', 'url:http,https', 'max:2048'],
            'code' => ['sometimes', 'alpha_num:ascii', 'min:4', 'max:12', 'unique:urls,code'],
        ];
    }

    /**
     * Code court personnalisé demandé, sinon null.
     */
    public function customCode(): ?string
    {
        $code = $this->string('code')->toString();

        return $code !== '' ? $code : null;
    }
}

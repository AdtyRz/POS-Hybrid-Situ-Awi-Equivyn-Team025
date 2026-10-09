<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PanggilPelayanApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meja_id' => ['required', 'integer', 'exists:meja_makan,id'],
            'qr_token' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'meja_id.required' => 'Meja wajib dipilih.',
            'meja_id.exists' => 'Meja tidak ditemukan.',
        ];
    }
}

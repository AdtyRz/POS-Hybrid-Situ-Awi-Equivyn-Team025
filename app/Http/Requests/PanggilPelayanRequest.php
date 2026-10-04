<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PanggilPelayanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_meja' => ['required', 'string', 'max:10', 'exists:meja_makan,kode_meja'],
            'qr_token' => ['required', 'string', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_meja.required' => 'Kode meja wajib diisi.',
            'kode_meja.exists' => 'Meja tidak ditemukan.',
            'qr_token.required' => 'Token QR wajib diisi.',
        ];
    }
}
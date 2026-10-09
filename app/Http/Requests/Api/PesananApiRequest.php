<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PesananApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meja_id' => ['required', 'integer', 'exists:meja_makan,id'],
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['required', 'integer', 'min:1', 'max:99'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'meja_id.required' => 'Meja wajib dipilih.',
            'meja_id.exists' => 'Meja tidak ditemukan.',
            'jumlah.required' => 'Minimal 1 item harus dipesan.',
            'jumlah.min' => 'Minimal 1 item harus dipesan.',
            'jumlah.*.required' => 'Jumlah item wajib diisi.',
            'jumlah.*.min' => 'Jumlah minimal 1.',
            'jumlah.*.max' => 'Jumlah maksimal 99.',
            'catatan.*.max' => 'Catatan terlalu panjang.',
        ];
    }
}

<?php

namespace App\Http\Requests\Api;

use App\Models\Pesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateStatusApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_pesanan' => ['required', 'in:diproses,siap,diantar'],
        ];
    }

    public function messages(): array
    {
        return [
            'status_pesanan.required' => 'Status pesanan wajib diisi.',
            'status_pesanan.in' => 'Status tidak sah.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $kode = $this->route('kode');
            if (! $kode) {
                return;
            }

            $pesanan = Pesanan::where('kode_pesanan', $kode)->first();
            if (! $pesanan) {
                return;
            }

            $allowed = $this->allowedNext($pesanan->status_pesanan);
            if (! in_array($this->input('status_pesanan'), $allowed)) {
                $v->errors()->add('status_pesanan', 'Transisi status tidak diizinkan.');
            }

            if ($pesanan->status_pesanan === Pesanan::STATUS_SELESAI) {
                $v->errors()->add('status_pesanan', 'Pesanan sudah selesai, tidak bisa diubah.');
            }
        });
    }

    private function allowedNext(string $current): array
    {
        return match ($current) {
            Pesanan::STATUS_MENUNGGU => [Pesanan::STATUS_DIPROSES],
            Pesanan::STATUS_DIPROSES => [Pesanan::STATUS_SIAP],
            Pesanan::STATUS_SIAP => [Pesanan::STATUS_DIANTAR],
            Pesanan::STATUS_DIANTAR => [Pesanan::STATUS_SELESAI],
            Pesanan::STATUS_SELESAI => [],
            default => [],
        };
    }
}

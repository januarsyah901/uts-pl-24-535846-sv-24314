<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $kamarId = $this->route('kamar');
        if (is_object($kamarId)) {
            $kamarId = $kamarId->id;
        }

        return [
            'nomor_kamar' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('kamars', 'nomor_kamar')->ignore($kamarId),
            ],
            'tipe' => 'sometimes|required|in:AC,Non-AC',
            'harga_sewa' => 'sometimes|required|integer|min:100000',
            'status' => 'sometimes|required|in:Tersedia,Terisi,Perbaikan',
            'fasilitas' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'nomor_kamar.unique' => 'Nomor kamar sudah terdaftar.',
            'tipe.required' => 'Tipe kamar wajib dipilih.',
            'tipe.in' => 'Tipe kamar harus AC atau Non-AC.',
            'harga_sewa.required' => 'Harga sewa wajib diisi.',
            'harga_sewa.integer' => 'Harga sewa harus berupa angka bulat.',
            'harga_sewa.min' => 'Harga sewa minimal Rp 100.000.',
            'status.required' => 'Status kamar wajib dipilih.',
            'status.in' => 'Status kamar harus Tersedia, Terisi, atau Perbaikan.',
        ];
    }
}

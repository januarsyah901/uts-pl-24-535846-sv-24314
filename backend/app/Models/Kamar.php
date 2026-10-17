<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_kamar',
        'tipe',
        'harga_sewa',
        'status',
        'fasilitas',
    ];

    protected function casts(): array
    {
        return [
            'harga_sewa' => 'integer',
        ];
    }
}

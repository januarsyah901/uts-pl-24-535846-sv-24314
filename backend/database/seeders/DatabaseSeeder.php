<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Pengguna default: Ibu Kos
        User::firstOrCreate(
            ['email' => 'ibukos@sikos.id'],
            [
                'name' => 'Ibu Hj. Siti Nurjanah',
                'password' => Hash::make('password123'),
            ]
        );

        // Data awal kamar kos
        $kamars = [
            [
                'nomor_kamar' => 'K-101',
                'tipe' => 'AC',
                'harga_sewa' => 1500000,
                'status' => 'Tersedia',
                'fasilitas' => 'AC, Kamar mandi dalam, Kasur queen size, Lemari 2 pintu, Wi-Fi 50 Mbps',
            ],
            [
                'nomor_kamar' => 'K-102',
                'tipe' => 'AC',
                'harga_sewa' => 1400000,
                'status' => 'Terisi',
                'fasilitas' => 'AC, Kamar mandi dalam, Kasur single, Meja belajar, Lemari',
            ],
            [
                'nomor_kamar' => 'K-201',
                'tipe' => 'Non-AC',
                'harga_sewa' => 850000,
                'status' => 'Tersedia',
                'fasilitas' => 'Kipas angin plafon, Kamar mandi luar, Kasur single, Lemari, Wi-Fi',
            ],
            [
                'nomor_kamar' => 'K-202',
                'tipe' => 'Non-AC',
                'harga_sewa' => 800000,
                'status' => 'Perbaikan',
                'fasilitas' => 'Sedang renovasi instalasi air dan cat dinding',
            ],
        ];

        foreach ($kamars as $kamar) {
            Kamar::firstOrCreate(
                ['nomor_kamar' => $kamar['nomor_kamar']],
                $kamar
            );
        }
    }
}

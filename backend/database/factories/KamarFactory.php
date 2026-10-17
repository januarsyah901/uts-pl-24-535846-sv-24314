<?php

namespace Database\Factories;

use App\Models\Kamar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kamar>
 */
class KamarFactory extends Factory
{
    protected $model = Kamar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_kamar' => 'K-' . fake()->unique()->numerify('###'),
            'tipe' => fake()->randomElement(['AC', 'Non-AC']),
            'harga_sewa' => fake()->randomElement([600000, 750000, 900000, 1200000, 1500000]),
            'status' => fake()->randomElement(['Tersedia', 'Terisi', 'Perbaikan']),
            'fasilitas' => 'Kasur, Lemari pakaian, Wi-Fi, Meja belajar',
        ];
    }
}

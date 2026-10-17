<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KamarApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_to_kamars_endpoint_returns_401(): void
    {
        $response = $this->getJson('/api/kamars');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_authenticated_user_can_list_all_kamars(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Kamar::factory()->count(3)->create();

        $response = $this->getJson('/api/kamars');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'nomor_kamar', 'tipe', 'harga_sewa', 'status', 'fasilitas'],
                ],
            ]);
    }

    public function test_authenticated_user_can_create_kamar_returns_201(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'nomor_kamar' => 'K-301',
            'tipe' => 'AC',
            'harga_sewa' => 1250000,
            'status' => 'Tersedia',
            'fasilitas' => 'AC, Meja Belajar, Kasur Single',
        ];

        $response = $this->postJson('/api/kamars', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Kamar kos berhasil ditambahkan.',
                'data' => [
                    'nomor_kamar' => 'K-301',
                    'tipe' => 'AC',
                    'harga_sewa' => 1250000,
                    'status' => 'Tersedia',
                ],
            ]);

        $this->assertDatabaseHas('kamars', [
            'nomor_kamar' => 'K-301',
        ]);
    }

    public function test_create_kamar_with_invalid_data_returns_422(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/kamars', [
            'nomor_kamar' => '',
            'tipe' => 'Super-VIP-Not-Allowed',
            'harga_sewa' => 500, // di bawah minimum 100000
            'status' => 'InvalidStatus',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nomor_kamar', 'tipe', 'harga_sewa', 'status']);
    }

    public function test_authenticated_user_can_view_single_kamar(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $kamar = Kamar::factory()->create([
            'nomor_kamar' => 'K-109',
        ]);

        $response = $this->getJson('/api/kamars/' . $kamar->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Detail kamar berhasil diambil.',
                'data' => [
                    'id' => $kamar->id,
                    'nomor_kamar' => 'K-109',
                ],
            ]);
    }

    public function test_authenticated_user_can_update_kamar(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $kamar = Kamar::factory()->create([
            'nomor_kamar' => 'K-110',
            'status' => 'Tersedia',
            'harga_sewa' => 1000000,
        ]);

        $response = $this->putJson('/api/kamars/' . $kamar->id, [
            'status' => 'Terisi',
            'harga_sewa' => 1100000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Informasi kamar kos berhasil diperbarui.',
                'data' => [
                    'id' => $kamar->id,
                    'status' => 'Terisi',
                    'harga_sewa' => 1100000,
                ],
            ]);

        $this->assertDatabaseHas('kamars', [
            'id' => $kamar->id,
            'status' => 'Terisi',
            'harga_sewa' => 1100000,
        ]);
    }

    public function test_authenticated_user_can_delete_kamar(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $kamar = Kamar::factory()->create([
            'nomor_kamar' => 'K-999',
        ]);

        $response = $this->deleteJson('/api/kamars/' . $kamar->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Kamar kos berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('kamars', [
            'id' => $kamar->id,
        ]);
    }
}

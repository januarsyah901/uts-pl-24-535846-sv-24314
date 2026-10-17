<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKamarRequest;
use App\Http\Requests\UpdateKamarRequest;
use App\Models\Kamar;
use Illuminate\Http\JsonResponse;

class KamarController extends Controller
{
    /**
     * Menampilkan seluruh daftar kamar kos.
     */
    public function index(): JsonResponse
    {
        $kamars = Kamar::query()->latest('id')->get();

        return response()->json([
            'message' => 'Daftar kamar berhasil diambil.',
            'data' => $kamars,
        ], 200);
    }

    /**
     * Menyimpan kamar kos baru.
     */
    public function store(StoreKamarRequest $request): JsonResponse
    {
        $kamar = Kamar::create($request->validated());

        return response()->json([
            'message' => 'Kamar kos berhasil ditambahkan.',
            'data' => $kamar,
        ], 201);
    }

    /**
     * Menampilkan detail satu kamar kos.
     */
    public function show(Kamar $kamar): JsonResponse
    {
        return response()->json([
            'message' => 'Detail kamar berhasil diambil.',
            'data' => $kamar,
        ], 200);
    }

    /**
     * Memperbarui informasi kamar kos.
     */
    public function update(UpdateKamarRequest $request, Kamar $kamar): JsonResponse
    {
        $kamar->update($request->validated());

        return response()->json([
            'message' => 'Informasi kamar kos berhasil diperbarui.',
            'data' => $kamar,
        ], 200);
    }

    /**
     * Menghapus unit kamar kos.
     */
    public function destroy(Kamar $kamar): JsonResponse
    {
        $kamar->delete();

        return response()->json([
            'message' => 'Kamar kos berhasil dihapus.',
        ], 200);
    }
}

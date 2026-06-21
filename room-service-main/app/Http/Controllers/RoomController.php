<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoomController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Daftar room berhasil diambil',
            'data' => Room::orderBy('id', 'asc')->get(),
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail room berhasil diambil',
            'data' => $room,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_number' => 'required|string|unique:rooms,room_number',
            'type' => 'required|string',
            'price' => 'required|integer|min:0',
            'capacity' => 'required|integer|min:1',
            'status' => 'nullable|string|in:available,booked',
        ]);

        $room = Room::create([
            'room_number' => $validated['room_number'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'] ?? 'available',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil disimpan',
            'data' => $room,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'room_number' => 'sometimes|required|string|unique:rooms,room_number,' . $room->id,
            'type' => 'sometimes|required|string',
            'price' => 'sometimes|required|integer|min:0',
            'capacity' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|string|in:available,booked',
        ]);

        $room->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil diperbarui',
            'data' => $room,
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil dihapus',
        ], 200);
    }

    public function available(): JsonResponse
    {
        $rooms = Room::where('status', 'available')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar room available berhasil diambil',
            'data' => $rooms,
        ], 200);
    }

    public function bookings(int $id): JsonResponse
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        try {
            $response = Http::baseUrl(config('services.booking_service.url'))
                ->timeout(config('services.booking_service.timeout'))
                ->retry(
                    config('services.booking_service.retries'),
                    config('services.booking_service.retry_delay_ms')
                )
                ->get('/bookings', ['room_id' => $id]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $payload = $response->json();
            $bookings = is_array($payload) && isset($payload['data']) && is_array($payload['data'])
                ? $payload['data']
                : (is_array($payload) ? $payload : []);

            return response()->json([
                'success' => true,
                'message' => 'Data booking room berhasil diambil',
                'room' => $room,
                'data' => $bookings,
            ], 200);
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Booking service tidak dapat diakses',
            ], 503);
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data booking dari booking-service',
                'error' => $e->response?->json() ?? $e->getMessage(),
            ], 502);
        }
    }

    public function reserve(int $id): JsonResponse
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        if ($room->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Room sedang tidak tersedia',
                'data' => $room,
            ], 400);
        }

        $room->status = 'booked';
        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil di-reserve',
            'data' => $room,
        ], 200);
    }

    public function release(int $id): JsonResponse
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room tidak ditemukan',
            ], 404);
        }

        if ($room->status === 'available') {
            return response()->json([
                'success' => true,
                'message' => 'Room sudah available',
                'data' => $room,
            ], 200);
        }

        $room->status = 'available';
        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Room berhasil di-release',
            'data' => $room,
        ], 200);
    }
}

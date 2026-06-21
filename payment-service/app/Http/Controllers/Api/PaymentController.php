<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query()->latest();

        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar pembayaran berhasil diambil',
            'data' => $query->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pembayaran berhasil diambil',
            'data' => $payment,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string|in:cash,transfer,qris,card',
            'notes' => 'nullable|string',
        ]);

        $booking = $this->getBooking($validated['booking_id']);

        if ($booking instanceof JsonResponse) {
            return $booking;
        }

        $payment = Payment::create([
            'booking_id' => $validated['booking_id'],
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment berhasil dibuat dengan status pending',
            'booking' => $booking,
            'data' => $payment,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'method' => 'sometimes|string|in:cash,transfer,qris,card',
            'status' => 'sometimes|string|in:pending,paid,failed,cancelled,refunded',
            'payment_reference' => 'nullable|string|unique:payments,payment_reference,' . $payment->id,
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment berhasil diperbarui',
            'data' => $payment->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment tidak ditemukan',
            ], 404);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment berhasil dihapus',
        ]);
    }

    public function pay(int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment tidak ditemukan',
            ], 404);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Payment sudah dibayar sebelumnya',
                'data' => $payment,
            ]);
        }

        $payment->update([
            'status' => 'paid',
            'payment_reference' => $payment->payment_reference ?: 'PAY-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'paid_at' => now(),
        ]);

        $this->updateBookingStatus($payment->booking_id, 'paid');

        return response()->json([
            'success' => true,
            'message' => 'Payment berhasil dibayar dan booking diupdate menjadi paid',
            'data' => $payment->fresh(),
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        return $this->changeStatus($id, 'cancelled', 'Payment berhasil dibatalkan');
    }

    public function refund(int $id): JsonResponse
    {
        return $this->changeStatus($id, 'refunded', 'Payment berhasil direfund');
    }

    public function bookingPayments(int $bookingId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Daftar payment berdasarkan booking berhasil diambil',
            'data' => Payment::where('booking_id', $bookingId)->latest()->get(),
        ]);
    }

    public function processAsync(int $id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment tidak ditemukan',
            ], 404);
        }

        \App\Jobs\ProcessPaymentJob::dispatch($payment->id)->onQueue('payment_queue');

        return response()->json([
            'success' => true,
            'message' => 'Payment sedang diproses melalui RabbitMQ queue payment_queue',
            'data' => $payment,
        ], 202);
    }

    private function changeStatus(int $id, string $status, string $message): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment tidak ditemukan',
            ], 404);
        }

        $payment->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $payment->fresh(),
        ]);
    }

    private function getBooking(int $bookingId): array|JsonResponse
    {
        try {
            $response = Http::baseUrl(config('services.booking_service.url'))
                ->timeout(config('services.booking_service.timeout'))
                ->retry(
                    config('services.booking_service.retries'),
                    config('services.booking_service.retry_delay_ms')
                )
                ->get('/bookings/' . $bookingId);

            if ($response->status() === 404) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking tidak ditemukan di booking-service',
                ], 404);
            }

            if ($response->failed()) {
                throw new RequestException($response);
            }

            return $response->json();
        } catch (ConnectionException) {
            return response()->json([
                'success' => false,
                'message' => 'Booking service tidak dapat diakses',
            ], 503);
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal validasi booking ke booking-service',
                'error' => $e->response?->json() ?? $e->getMessage(),
            ], 502);
        }
    }

    private function updateBookingStatus(int $bookingId, string $status): void
    {
        try {
            Http::baseUrl(config('services.booking_service.url'))
                ->timeout(config('services.booking_service.timeout'))
                ->retry(
                    config('services.booking_service.retries'),
                    config('services.booking_service.retry_delay_ms')
                )
                ->put('/bookings/' . $bookingId, [
                    'status' => $status,
                ]);
        } catch (\Throwable) {
            // Jangan gagalkan payment jika booking-service sedang tidak dapat diakses.
            // Error detail dapat dilihat di log Laravel.
        }
    }
}
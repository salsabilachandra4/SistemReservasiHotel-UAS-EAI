<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::all();

        return response()->json([
            'success' => true,
            'message' => 'Daftar customer berhasil diambil',
            'data' => $customers
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail customer berhasil diambil',
            'data' => $customer
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data customer berhasil disimpan',
            'data' => $customer
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data customer berhasil diperbarui',
            'data' => $customer
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data customer berhasil dihapus'
        ], 200);
    }

    public function bookings(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        try {
            $response = Http::baseUrl(config('services.booking_service.url'))
                ->timeout(config('services.booking_service.timeout'))
                ->retry(
                    config('services.booking_service.retries'),
                    config('services.booking_service.retry_delay_ms')
                )
                ->get('/bookings', [
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                ]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $payload = $response->json();
            $bookings = is_array($payload) && isset($payload['data']) && is_array($payload['data'])
                ? $payload['data']
                : (is_array($payload) ? $payload : []);

            $customerBookings = collect($bookings)
                ->filter(function ($booking) use ($customer) {
                    $bookingName = strtolower((string) ($booking['customer_name'] ?? ''));
                    $bookingPhone = (string) ($booking['customer_phone'] ?? '');

                    return $bookingName === strtolower($customer->name)
                        && $bookingPhone === $customer->phone;
                })
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'message' => 'Data booking customer berhasil diambil',
                'customer' => $customer,
                'data' => $customerBookings
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
}

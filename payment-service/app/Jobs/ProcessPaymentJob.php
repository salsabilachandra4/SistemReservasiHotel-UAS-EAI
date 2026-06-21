<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $paymentId)
    {
    }

    public function handle(): void
    {
        $payment = Payment::find($this->paymentId);

        if (!$payment || $payment->status === 'paid') {
            return;
        }

        $payment->update([
            'status' => 'paid',
            'payment_reference' => $payment->payment_reference ?: 'PAY-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'paid_at' => now(),
        ]);

        try {
            Http::baseUrl(config('services.booking_service.url'))
                ->timeout(config('services.booking_service.timeout'))
                ->retry(
                    config('services.booking_service.retries'),
                    config('services.booking_service.retry_delay_ms')
                )
                ->put('/bookings/' . $payment->booking_id, [
                    'status' => 'paid',
                ]);
        } catch (\Throwable) {
        }
    }
}
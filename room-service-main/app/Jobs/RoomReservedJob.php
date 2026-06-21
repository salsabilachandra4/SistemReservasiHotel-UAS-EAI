<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RoomReservedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingId;
    public $isSuccess;

    public function __construct($bookingId, $isSuccess)
    {
        $this->bookingId = $bookingId;
        $this->isSuccess = $isSuccess;
    }

    public function handle()
    {
        // Only handled by booking-service
    }
}

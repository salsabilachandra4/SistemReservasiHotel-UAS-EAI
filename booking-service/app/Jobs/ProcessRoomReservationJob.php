<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRoomReservationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingId;
    public $roomId;

    public function __construct($bookingId, $roomId)
    {
        $this->bookingId = $bookingId;
        $this->roomId = $roomId;
    }

    public function handle()
    {
        // Only handled by room-service
    }
}

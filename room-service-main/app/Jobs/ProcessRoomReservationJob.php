<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Room;

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
        $room = Room::find($this->roomId);
        $isSuccess = false;

        if ($room && $room->status === 'available') {
            $room->status = 'booked';
            $room->save();
            $isSuccess = true;
        }

        // Reply to booking-service queue
        \App\Jobs\RoomReservedJob::dispatch($this->bookingId, $isSuccess)->onQueue('booking_queue');
    }
}

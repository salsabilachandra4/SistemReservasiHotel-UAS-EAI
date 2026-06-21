<?php

namespace App\GraphQL\Queries;

use App\Models\Booking;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Http;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class BookingDetailQuery extends Query
{
    protected $attributes = [
        'name' => 'bookingDetail',
        'description' => 'Mengambil detail booking beserta data room',
    ];

    public function type(): Type
    {
        return GraphQL::type('BookingDetail');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'ID booking',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $booking = Booking::find($args['id']);

        if (!$booking) {
            return null;
        }

        $room = null;

        try {
            $roomResponse = Http::get('http://room-nginx/api/rooms/' . $booking->room_id);

            if ($roomResponse->successful()) {
                $room = $roomResponse->json();
            }
        } catch (\Exception $e) {
            $room = null;
        }

        return [
            'id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'room_id' => $booking->room_id,
            'status' => $booking->status,
            'room_number' => $room['room_number'] ?? null,
            'room_type' => $room['type'] ?? null,
            'room_price' => $room['price'] ?? null,
        ];
    }
}

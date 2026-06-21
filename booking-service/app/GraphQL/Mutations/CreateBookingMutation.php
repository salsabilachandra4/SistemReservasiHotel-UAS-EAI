<?php

namespace App\GraphQL\Mutations;

use App\Models\Booking;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class CreateBookingMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createBooking',
        'description' => 'Membuat data booking baru',
    ];

    public function type(): Type
    {
        return GraphQL::type('Booking');
    }

    public function args(): array
    {
        return [
            'customer_id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'room_id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'checkin_date' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'checkout_date' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'total_price' => [
                'type' => Type::nonNull(Type::float()),
            ],
            'status' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        return Booking::create([
            'customer_id' => $args['customer_id'],
            'room_id' => $args['room_id'],
            'checkin_date' => $args['checkin_date'],
            'checkout_date' => $args['checkout_date'],
            'total_price' => $args['total_price'],
            'status' => $args['status'] ?? 'pending',
        ]);
    }
}

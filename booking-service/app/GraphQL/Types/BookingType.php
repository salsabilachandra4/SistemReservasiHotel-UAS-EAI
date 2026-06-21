<?php

namespace App\GraphQL\Types;

use App\Models\Booking;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class BookingType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Booking',
        'description' => 'Data booking hotel',
        'model' => Booking::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'customer_id' => [
                'type' => Type::int(),
            ],
            'room_id' => [
                'type' => Type::int(),
            ],
            'checkin_date' => [
                'type' => Type::string(),
            ],
            'checkout_date' => [
                'type' => Type::string(),
            ],
            'total_price' => [
                'type' => Type::float(),
            ],
            'status' => [
                'type' => Type::string(),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
            'updated_at' => [
                'type' => Type::string(),
            ],
        ];
    }
}

<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class BookingDetailType extends GraphQLType
{
    protected $attributes = [
        'name' => 'BookingDetail',
        'description' => 'Detail booking dengan data room',
    ];

    public function fields(): array
    {
        return [
            'id' => ['type' => Type::int()],
            'customer_id' => ['type' => Type::int()],
            'room_id' => ['type' => Type::int()],
            'status' => ['type' => Type::string()],
            'room_number' => ['type' => Type::string()],
            'room_type' => ['type' => Type::string()],
            'room_price' => ['type' => Type::float()],
        ];
    }
}

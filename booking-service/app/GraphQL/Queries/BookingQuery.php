<?php

namespace App\GraphQL\Queries;

use App\Models\Booking;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class BookingQuery extends Query
{
    protected $attributes = [
        'name' => 'booking',
        'description' => 'Mengambil detail booking berdasarkan ID',
    ];

    public function type(): Type
    {
        return GraphQL::type('Booking');
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
        return Booking::find($args['id']);
    }
}

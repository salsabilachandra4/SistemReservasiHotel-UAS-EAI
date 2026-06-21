<?php

namespace App\GraphQL\Queries;

use App\Models\Booking;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class BookingsQuery extends Query
{
    protected $attributes = [
        'name' => 'bookings',
        'description' => 'Mengambil semua data booking',
    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Booking'));
    }

    public function resolve($root, $args)
    {
        return Booking::all();
    }
}

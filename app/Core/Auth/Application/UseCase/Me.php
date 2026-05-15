<?php

namespace App\Core\Auth\Application\UseCase;

use Illuminate\Support\Facades\Auth;

final readonly class Me
{
    public function execute(): array
    {
        $user = Auth::user();
        $house = $user->house;
        return [
            'user' => [
                'id' => $house->public_id,
                'name' => $house->name,
                'email' => $house->email,
            ],
            'house' => [
                'id' => $house->public_id,
                'name' => $house->name,
            ]
        ];
    }
}

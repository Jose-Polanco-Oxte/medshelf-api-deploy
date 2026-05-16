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
                'id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'house' => $house ? [
                'id' => $house->public_id,
                'name' => $house->name,
            ] : null,
        ];
    }
}

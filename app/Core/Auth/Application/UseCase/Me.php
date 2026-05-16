<?php

namespace App\Core\Auth\Application\UseCase;

use Illuminate\Support\Facades\Auth;

final readonly class Me
{
    public function execute(): array
    {
        $user = Auth::user();
        return [
            'id' => $user->public_id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}

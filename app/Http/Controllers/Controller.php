<?php

namespace App\Http\Controllers;

use App\Core\Shared\Application\AppException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class Controller
{
    protected function getAuthHouseId(): string
    {
        return JWTAuth::payload()->get('house_id')
            ?? throw new AppException('Invalid token');
    }

    protected function getAuthUserId(): string
    {
        return Auth::user()->public_id;
    }
}

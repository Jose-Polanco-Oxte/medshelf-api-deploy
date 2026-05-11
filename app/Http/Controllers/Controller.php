<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class Controller
{
    protected function getAuthHouseId(): string
    {
        return JWTAuth::payload()->get('house_id');
    }

    protected function getAuthUserId(): string
    {
        return Auth::user()->public_id;
    }
}

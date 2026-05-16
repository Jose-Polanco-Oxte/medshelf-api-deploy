<?php

namespace Tests\Feature\Traits;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

trait WithJwtAuth
{
    /**
     * Returns HTTP headers containing a valid JWT Bearer token for the given user.
     * Pass these to any test HTTP call that requires authentication.
     */
    protected function authHeaders(User $user, array $extra = []): array
    {
        $token = JWTAuth::fromUser($user);

        return array_merge(['Authorization' => 'Bearer ' . $token], $extra);
    }
}

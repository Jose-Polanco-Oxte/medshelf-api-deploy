<?php

namespace App\Providers\Core;

use App\Core\Shared\Application\TransactionManager;
use Illuminate\Support\Facades\DB;

/**
 * Infrastructure adapter for TransactionManager.
 *
 * Wraps Eloquent's DB::transaction() so the Application layer
 * never imports any Laravel/DB primitives directly.
 */
final class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}

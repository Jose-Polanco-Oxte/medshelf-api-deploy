<?php

namespace App\Core\Shared\Application;

/**
 * Port: defines an atomic unit-of-work boundary.
 *
 * Implementations live in the infrastructure layer and must not leak
 * any framework details (e.g. DB facade) into the Application or Domain.
 */
interface TransactionManager
{
    /**
     * Execute $callback inside a single atomic transaction.
     *
     * The callback's return value is forwarded to the caller.
     * Any exception thrown inside the callback causes the transaction to roll back.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function run(callable $callback): mixed;
}

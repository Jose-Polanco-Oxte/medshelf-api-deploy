<?php

namespace App\Core\Shared\Application;

interface TransactionManager
{
    public function run(callable $callback): mixed;
}
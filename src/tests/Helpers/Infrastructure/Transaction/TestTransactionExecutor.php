<?php

namespace Tests\Helpers\Infrastructure\Transaction;

use App\UseCase\Share\TransactionExecutor;

class TestTransactionExecutor implements TransactionExecutor
{
    public function perform(callable $callback): void
    {
        $callback();
    }
}

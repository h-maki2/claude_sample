<?php

namespace App\Infrastructure\Transaction;

use App\UseCase\Share\TransactionExecutor;
use Illuminate\Support\Facades\DB;

class TransactionExecutorImpl implements TransactionExecutor
{
    public function perform(callable $callback): void
    {
        DB::transaction(static function () use ($callback): void {
            $callback();
        });
    }
}

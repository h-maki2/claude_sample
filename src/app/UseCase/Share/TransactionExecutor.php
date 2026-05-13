<?php

namespace App\UseCase\Share;

interface TransactionExecutor
{
    public function perform(callable $callback): void;
}

<?php

namespace App\Domains\Models\Share\Clock\Production;

use App\Domains\Models\Share\Clock\Clock;
use DateTimeImmutable;

class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

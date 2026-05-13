<?php

namespace Tests\Helpers\Domains\Models\Share\Clock;

use App\Domains\Models\Share\Clock\Clock;
use DateTimeImmutable;

class TestFixedClock implements Clock
{
    public function __construct(
        private DateTimeImmutable $fixedTime,
    ) {}

    public function setTestDateTime(DateTimeImmutable $dateTime): void
    {
        $this->fixedTime = $dateTime;
    }

    public function now(): DateTimeImmutable
    {
        return $this->fixedTime;
    }
}

<?php

namespace App\Domains\Models\Share\DomainEvent;

use DateTimeImmutable;

interface DomainEvent
{
    public function eventVersion(): int;
    public function occurredOn(): DateTimeImmutable;
}

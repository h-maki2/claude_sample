<?php

namespace App\Domains\Models\Share\DomainEvent;

interface DomainEventSubscriber
{
    public function subscribedToEventType(): string;
    public function handleEvent(DomainEvent $domainEvent): void;
}

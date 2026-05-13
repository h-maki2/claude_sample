<?php

namespace App\Domains\Models\Share\DomainEvent;

class DomainEventPublisher
{
    /** @var DomainEventSubscriber[] */
    private array $subscriberList = [];

    public function subscribe(DomainEventSubscriber $subscriber): void
    {
        $this->subscriberList[] = $subscriber;
    }

    public function publish(DomainEvent $event): void
    {
        foreach ($this->subscriberList as $subscriber) {
            if ($subscriber->subscribedToEventType() === get_class($event)
                || $subscriber->subscribedToEventType() === DomainEvent::class) {
                $subscriber->handleEvent($event);
            }
        }
    }
}

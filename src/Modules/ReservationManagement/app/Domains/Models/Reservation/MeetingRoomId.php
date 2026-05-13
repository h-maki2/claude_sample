<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

class MeetingRoomId
{
    public function __construct(readonly string $value) {}

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

<?php

namespace Modules\ReservationManagement\UseCase\CreateReservation;

use DateTimeImmutable;

class CreateReservationInput
{
    public function __construct(
        readonly string $meetingRoomId,
        readonly string $name,
        readonly string $contactPerson,
        readonly string $email,
        readonly DateTimeImmutable $startAt,
        readonly DateTimeImmutable $endAt,
    ) {}
}

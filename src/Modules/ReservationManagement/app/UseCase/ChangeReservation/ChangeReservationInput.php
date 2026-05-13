<?php

namespace Modules\ReservationManagement\UseCase\ChangeReservation;

use DateTimeImmutable;

class ChangeReservationInput
{
    public function __construct(
        readonly string          $reservationId,
        readonly string          $name,
        readonly DateTimeImmutable $startAt,
        readonly DateTimeImmutable $endAt,
    ) {}
}

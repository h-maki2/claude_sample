<?php

namespace Modules\ReservationManagement\UseCase\ListReservations;

use DateTimeImmutable;

class ReservationListItem
{
    public function __construct(
        readonly string $reservationId,
        readonly string $meetingRoomId,
        readonly string $meetingRoomName,
        readonly DateTimeImmutable $startAt,
        readonly DateTimeImmutable $endAt,
        readonly string $status,
    ) {}
}

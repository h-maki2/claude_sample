<?php

namespace Modules\ReservationManagement\Contracts\Reservation;

interface ReservationExistenceChecker
{
    public function hasActiveReservationsByMeetingRoomId(string $meetingRoomId): bool;
}

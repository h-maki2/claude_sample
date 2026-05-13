<?php

namespace Modules\ReservationManagement\Infrastructure\Reservation;

use Modules\ReservationManagement\Contracts\Reservation\ReservationExistenceChecker;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation\ReservationModel;

class EloquentReservationExistenceChecker implements ReservationExistenceChecker
{
    public function hasActiveReservationsByMeetingRoomId(string $meetingRoomId): bool
    {
        return ReservationModel::where('meeting_room_id', $meetingRoomId)
            ->where('status', ReservationStatus::CONFIRMED->value)
            ->where('ended_at', '>', now())
            ->exists();
    }
}

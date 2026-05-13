<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

class BufferTime
{
    public const MINUTES = 10;

    public function isSatisfiedBetween(
        ReservationList $reservationList,
        ReservationTimeRange $following,
    ): bool {
        $preceding = $reservationList->latestEndingTimeRangeBefore($following);

        if ($preceding === null) {
            return true;
        }

        $requiredGapSeconds = self::MINUTES * 60;
        $actualGapSeconds = $following->startAt->getTimestamp() - $preceding->endAt->getTimestamp();

        return $actualGapSeconds >= $requiredGapSeconds;
    }
}

<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

use App\Domains\Models\Share\Clock\Clock;
use DateTimeImmutable;

class ReservablePeriod
{
    private const MAX_DAYS_AHEAD = 14;

    public function isSatisfiedBy(ReservationTimeRange $reservationTimeRange, Clock $clock): bool
    {
        $today = DateTimeImmutable::createFromFormat('Y-m-d', $clock->now()->format('Y-m-d'));
        $maxDate = $today->modify('+' . self::MAX_DAYS_AHEAD . ' days');
        $normalizedDate = DateTimeImmutable::createFromFormat('Y-m-d', $reservationTimeRange->startAt->format('Y-m-d'));

        return $normalizedDate >= $today && $normalizedDate <= $maxDate;
    }
}

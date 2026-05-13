<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

use DateTimeImmutable;
use InvalidArgumentException;

class ReservationTimeRange
{
    private const BUSINESS_START_MINUTES = 8 * 60;
    private const BUSINESS_END_MINUTES = 22 * 60;
    private const MIN_DURATION_MINUTES = 30;
    private const MAX_DURATION_MINUTES = 240;

    public function __construct(
        readonly DateTimeImmutable $startAt,
        readonly DateTimeImmutable $endAt,
    ) {
        if ($endAt <= $startAt) {
            throw new InvalidArgumentException('終了時刻は開始時刻より後でなければなりません。');
        }

        $startMinutes = (int)$startAt->format('H') * 60 + (int)$startAt->format('i');
        $endMinutes = (int)$endAt->format('H') * 60 + (int)$endAt->format('i');

        if ($startMinutes < self::BUSINESS_START_MINUTES || $endMinutes > self::BUSINESS_END_MINUTES) {
            throw new InvalidArgumentException('予約時間は営業時間（8:00〜22:00）内である必要があります。');
        }

        $durationMinutes = ($endAt->getTimestamp() - $startAt->getTimestamp()) / 60;

        if ($durationMinutes < self::MIN_DURATION_MINUTES) {
            throw new InvalidArgumentException('予約時間は最短30分です。');
        }

        if ($durationMinutes > self::MAX_DURATION_MINUTES) {
            throw new InvalidArgumentException('予約時間は最長4時間です。');
        }
    }

    public function equals(self $other): bool
    {
        return $this->startAt == $other->startAt && $this->endAt == $other->endAt;
    }

    public function isOverlapping(self $other): bool
    {
        return $this->startAt < $other->endAt && $this->endAt > $other->startAt;
    }

    public function endsBefore(self $other): bool
    {
        return $this->endAt <= $other->startAt;
    }

    public function endsAfter(self $other): bool
    {
        return $this->endAt > $other->endAt;
    }
}

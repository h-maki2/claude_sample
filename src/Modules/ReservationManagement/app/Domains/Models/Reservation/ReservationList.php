<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

/** @implements \IteratorAggregate<int, Reservation> */
class ReservationList implements \IteratorAggregate
{
    /**
     * @param Reservation[] $reservations
     */
    public function __construct(
        private array $reservations,
    ) {}

    public function isEmpty(): bool
    {
        return count($this->reservations) === 0;
    }

    /** @return \ArrayIterator<int, Reservation> */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->reservations);
    }

    public function latestEndingTimeRangeBefore(ReservationTimeRange $targetReservationTimeRange): ?ReservationTimeRange
    {
        $latestEndingTimeRange = null;

        foreach ($this->reservations as $reservation) {
            $timeRange = $reservation->timeRange();
            if ($timeRange->endsBefore($targetReservationTimeRange)) {
                if ($latestEndingTimeRange === null || $timeRange->endsAfter($latestEndingTimeRange)) {
                    $latestEndingTimeRange = $timeRange;
                }
            }
        }

        return $latestEndingTimeRange;
    }

    public function excluding(ReservationId $excludingId): self
    {
        return new self(array_values(array_filter(
            $this->reservations,
            fn(Reservation $reservation) => $reservation->reservationId()->value !== $excludingId->value,
        )));
    }

    public function isOverlapping(ReservationTimeRange $targetReservationTimeRange): bool
    {
        foreach ($this->reservations as $reservation) {
            $timeRange = $reservation->timeRange();
            if ($timeRange->isOverlapping($targetReservationTimeRange)) {
                return true;
            }
        }

        return false;
    }
}

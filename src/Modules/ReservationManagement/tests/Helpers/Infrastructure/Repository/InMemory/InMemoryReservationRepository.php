<?php

namespace Tests\ReservationManagement\Helpers\Infrastructure\Repository\InMemory;

use DateTimeImmutable;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\Reservation;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationList;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationIdFactory;

class InMemoryReservationRepository implements ReservationRepository
{
    /** @var array<string, Reservation> */
    private array $store = [];

    public function nextId(): ReservationId
    {
        return TestReservationIdFactory::create();
    }

    public function findById(ReservationId $id): ?Reservation
    {
        return $this->store[$id->value] ?? null;
    }

    public function save(Reservation $reservation): void
    {
        $this->store[$reservation->reservationId()->value] = clone $reservation;
    }

    public function findActiveByDate(DateTimeImmutable $date): ReservationList
    {
        $dateString = $date->format('Y-m-d');

        return new ReservationList(array_values(array_filter(
            $this->store,
            fn(Reservation $reservation) =>
                $reservation->status() === ReservationStatus::CONFIRMED &&
                $reservation->timeRange()->startAt->format('Y-m-d') === $dateString,
        )));
    }

    public function findActiveByMeetingRoomIdAndDate(
        MeetingRoomId $meetingRoomId,
        DateTimeImmutable $date,
    ): ReservationList {
        $dateString = $date->format('Y-m-d');

        return new ReservationList(array_values(array_filter(
            $this->store,
            fn(Reservation $reservation) =>
                $reservation->status() === ReservationStatus::CONFIRMED &&
                $reservation->meetingRoomId()->value === $meetingRoomId->value &&
                $reservation->timeRange()->startAt->format('Y-m-d') === $dateString,
        )));
    }

}

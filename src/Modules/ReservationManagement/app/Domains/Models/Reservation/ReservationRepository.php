<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

use DateTimeImmutable;

interface ReservationRepository
{
    public function nextId(): ReservationId;

    public function findById(ReservationId $id): ?Reservation;

    public function save(Reservation $reservation): void;

    public function findActiveByDate(DateTimeImmutable $date): ReservationList;

    public function findActiveByMeetingRoomIdAndDate(
        MeetingRoomId $meetingRoomId,
        DateTimeImmutable $date,
    ): ReservationList;
}
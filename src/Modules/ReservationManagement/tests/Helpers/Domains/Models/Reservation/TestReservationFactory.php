<?php

namespace Tests\ReservationManagement\Helpers\Domains\Models\Reservation;

use DateTimeImmutable;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactEmail;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactPersonName;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\Reservation;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;

class TestReservationFactory
{
    public static function create(
        ?ReservationId $id = null,
        ?MeetingRoomId $meetingRoomId = null,
        ?ReservationName $name = null,
        ?ContactPersonName $contactPerson = null,
        ?ContactEmail $email = null,
        ?ReservationTimeRange $timeRange = null,
        ?ReservationStatus $status = null,
    ): Reservation {
        return new Reservation(
            $id ?? TestReservationIdFactory::create(),
            $meetingRoomId ?? new MeetingRoomId('01957b3c-1234-7abc-8def-000000000099'),
            $name ?? new ReservationName('第3回プロジェクト定例'),
            $contactPerson ?? new ContactPersonName('山田太郎'),
            $email ?? new ContactEmail('yamada@example.com'),
            $timeRange ?? new ReservationTimeRange(
                new DateTimeImmutable('2026-05-01 10:00:00'),
                new DateTimeImmutable('2026-05-01 11:30:00'),
            ),
            $status ?? ReservationStatus::CONFIRMED,
        );
    }
}

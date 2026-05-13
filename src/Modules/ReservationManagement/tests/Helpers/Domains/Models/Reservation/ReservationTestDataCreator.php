<?php

namespace Tests\ReservationManagement\Helpers\Domains\Models\Reservation;

use Modules\ReservationManagement\Domains\Models\Reservation\ContactEmail;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactPersonName;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\Reservation;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;

class ReservationTestDataCreator
{
    public function __construct(
        private ReservationRepository $repository,
    ) {}

    public function create(
        ?ReservationId $id = null,
        ?MeetingRoomId $meetingRoomId = null,
        ?ReservationName $name = null,
        ?ContactPersonName $contactPerson = null,
        ?ContactEmail $email = null,
        ?ReservationTimeRange $timeRange = null,
        ?ReservationStatus $status = null,
    ): Reservation {
        $reservation = TestReservationFactory::create($id, $meetingRoomId, $name, $contactPerson, $email, $timeRange, $status);
        $this->repository->save($reservation);
        return $reservation;
    }
}

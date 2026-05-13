<?php

namespace Tests\ReservationManagement\Helpers\Domains\Models\Reservation;

use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;

class TestReservationIdFactory
{
    public static function create(): ReservationId
    {
        return new ReservationId('01957b3c-1234-7abc-8def-000000000001');
    }
}

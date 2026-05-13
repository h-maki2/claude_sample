<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

enum ReservationStatus: int
{
    case CONFIRMED = 1;
    case CANCELLED = 2;

    public function label(): string
    {
        return match($this) {
            ReservationStatus::CONFIRMED => '予約確定',
            ReservationStatus::CANCELLED => 'キャンセル済み',
        };
    }
}

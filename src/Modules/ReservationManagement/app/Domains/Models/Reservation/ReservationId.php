<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

class ReservationId
{
    public function __construct(readonly string $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException('予約IDは空にできません。');
        }
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value)) {
            throw new \InvalidArgumentException('予約IDはUUIDv7形式で指定してください。');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

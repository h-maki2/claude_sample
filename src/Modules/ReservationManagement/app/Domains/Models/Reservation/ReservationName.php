<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

class ReservationName
{
    public function __construct(readonly string $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException('予約名は1文字以上で入力してください。');
        }
        if (mb_strlen($value) > 50) {
            throw new \InvalidArgumentException('予約名は50文字以内で入力してください。');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

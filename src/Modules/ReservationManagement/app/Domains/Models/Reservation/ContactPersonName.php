<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

class ContactPersonName
{
    public function __construct(readonly string $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException('担当者名は1文字以上で入力してください。');
        }
        if (mb_strlen($value) > 30) {
            throw new \InvalidArgumentException('担当者名は30文字以内で入力してください。');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

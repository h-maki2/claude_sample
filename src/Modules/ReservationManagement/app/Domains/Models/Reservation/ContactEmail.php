<?php

namespace Modules\ReservationManagement\Domains\Models\Reservation;

class ContactEmail
{
    public function __construct(readonly string $value)
    {
        if ($value === '') {
            throw new \InvalidArgumentException('連絡先メールアドレスは空にできません。');
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('連絡先メールアドレスは正しいメールアドレス形式で入力してください。');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

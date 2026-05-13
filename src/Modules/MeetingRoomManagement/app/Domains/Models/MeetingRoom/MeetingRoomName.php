<?php

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;

class MeetingRoomName
{
    public function __construct(
        readonly string $value
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('会議室名は1文字以上で入力してください。');
        }
        if (mb_strlen($value) > 50) {
            throw new \InvalidArgumentException('会議室名は50文字以内で入力してください。');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

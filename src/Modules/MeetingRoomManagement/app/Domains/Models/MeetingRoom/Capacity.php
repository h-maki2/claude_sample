<?php

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;

class Capacity
{
    public function __construct(
        readonly int $value
    ) {
        if ($value < 1) {
            throw new \InvalidArgumentException('定員は1名以上で入力してください。');
        }
        if ($value > 50) {
            throw new \InvalidArgumentException('定員は50名以下で入力してください。');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

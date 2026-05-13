<?php

namespace Modules\MeetingRoomManagement\Contracts\MeetingRoom;

class MeetingRoomDTO
{
    /**
     * @param array<int, int> $equipments
     */
    public function __construct(
        readonly string $meetingRoomId,
        readonly string $name,
        readonly int $capacity,
        readonly array $equipments,
    ) {}
}

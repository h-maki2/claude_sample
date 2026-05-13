<?php

namespace Modules\MeetingRoomManagement\UseCase\UpdateMeetingRoom;

class UpdateMeetingRoomInput
{
    /**
     * @param int[] $equipments Equipment::value の配列
     */
    public function __construct(
        readonly string $meetingRoomId,
        readonly string $name,
        readonly int $capacity,
        readonly array $equipments,
    ) {}
}

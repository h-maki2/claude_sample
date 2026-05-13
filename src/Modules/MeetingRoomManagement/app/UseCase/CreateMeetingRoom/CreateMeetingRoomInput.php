<?php

namespace Modules\MeetingRoomManagement\UseCase\CreateMeetingRoom;

class CreateMeetingRoomInput
{
    /**
     * @param int[] $equipments Equipment::value の配列
     */
    public function __construct(
        readonly string $name,
        readonly int $capacity,
        readonly array $equipments,
    ) {}
}

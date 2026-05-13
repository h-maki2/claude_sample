<?php

namespace Modules\MeetingRoomManagement\UseCase\ListMeetingRooms;

class MeetingRoomListItem
{
    /**
     * @param string[] $equipments 備品の表示名一覧（Equipment::label() の値）
     */
    public function __construct(
        readonly string $meetingRoomId,
        readonly string $name,
        readonly int $capacity,
        readonly array $equipments,
    ) {}
}

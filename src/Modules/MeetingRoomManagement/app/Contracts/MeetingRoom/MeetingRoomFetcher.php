<?php

namespace Modules\MeetingRoomManagement\Contracts\MeetingRoom;

interface MeetingRoomFetcher
{
    public function fetchById(string $meetingRoomId): ?MeetingRoomDTO;

    /** @return MeetingRoomDTO[] */
    public function fetchAll(): array;
}

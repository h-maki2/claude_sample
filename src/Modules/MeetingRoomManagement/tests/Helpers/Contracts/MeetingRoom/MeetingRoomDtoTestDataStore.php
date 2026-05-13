<?php

namespace Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom;

use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomDTO;

interface MeetingRoomDtoTestDataStore
{
    public function store(MeetingRoomDTO $meetingRoomDto): void;
}

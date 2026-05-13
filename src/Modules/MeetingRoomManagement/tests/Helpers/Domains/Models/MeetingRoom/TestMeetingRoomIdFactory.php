<?php

namespace Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;

class TestMeetingRoomIdFactory
{
    public static function create(): MeetingRoomId
    {
        return new MeetingRoomId('01957b3c-1234-7abc-8def-000000000001');
    }
}

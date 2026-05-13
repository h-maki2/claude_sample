<?php

namespace Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoom;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;

class TestMeetingRoomFactory
{
    public static function create(
        ?MeetingRoomId $id = null,
        ?MeetingRoomName $name = null,
        ?Capacity $capacity = null,
        ?array $equipments = null,
    ): MeetingRoom {
        return new MeetingRoom(
            $id ?? TestMeetingRoomIdFactory::create(),
            $name ?? new MeetingRoomName('第1会議室'),
            $capacity ?? new Capacity(10),
            $equipments ?? [],
        );
    }
}


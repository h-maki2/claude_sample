<?php

namespace Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoom;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;

class MeetingRoomTestDataCreator
{
    public function __construct(
        private MeetingRoomRepository $repository,
    ) {}

    public function create(
        ?MeetingRoomId $id = null,
        ?MeetingRoomName $name = null,
        ?Capacity $capacity = null,
        ?array $equipments = null,
    ): MeetingRoom {
        $room = TestMeetingRoomFactory::create($id, $name, $capacity, $equipments);
        $this->repository->save($room);
        return $room;
    }
}

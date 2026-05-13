<?php

namespace Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent;

use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomDTO;
use Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom\MeetingRoomModel;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataStore;

class EloquentMeetingRoomDtoTestDataStore implements MeetingRoomDtoTestDataStore
{
    public function store(MeetingRoomDTO $meetingRoomDto): void
    {
        $model = MeetingRoomModel::create([
            'meeting_room_id' => $meetingRoomDto->meetingRoomId,
            'name' => $meetingRoomDto->name,
            'capacity' => $meetingRoomDto->capacity,
        ]);

        foreach ($meetingRoomDto->equipments as $equipment) {
            $model->equipments()->create(['equipment' => $equipment]);
        }
    }
}

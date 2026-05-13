<?php

namespace Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom;

use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomDTO;

class TestMeetingRoomDtoFactory
{
    public static function create(
        ?string $meetingRoomId = null,
        ?string $name = null,
        ?int $capacity = null,
        ?array $equipments = null,
    ): MeetingRoomDTO {
        return new MeetingRoomDTO(
            meetingRoomId: $meetingRoomId ?? '01957b3c-1234-7abc-8def-000000000099',
            name: $name ?? '第1会議室',
            capacity: $capacity ?? 10,
            equipments: $equipments ?? [],
        );
    }
}

<?php

namespace Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom;

use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomDTO;

class MeetingRoomDtoTestDataCreator
{
    public function __construct(
        private MeetingRoomDtoTestDataStore $dataStore,
    ) {}

    public function create(
        ?string $meetingRoomId = null,
        ?string $name = null,
        ?int $capacity = null,
        ?array $equipments = null,
    ): MeetingRoomDTO {
        $meetingRoomDto = TestMeetingRoomDtoFactory::create(
            meetingRoomId: $meetingRoomId,
            name: $name,
            capacity: $capacity,
            equipments: $equipments,
        );

        $this->dataStore->store($meetingRoomDto);

        return $meetingRoomDto;
    }
}

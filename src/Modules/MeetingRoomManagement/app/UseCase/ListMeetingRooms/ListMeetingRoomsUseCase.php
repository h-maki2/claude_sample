<?php

namespace Modules\MeetingRoomManagement\UseCase\ListMeetingRooms;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoom;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;

class ListMeetingRoomsUseCase
{
    public function __construct(
        private MeetingRoomRepository $meetingRoomRepository,
    ) {}

    /** @return MeetingRoomListItem[] */
    public function execute(): array
    {
        $meetingRooms = $this->meetingRoomRepository->findAll();

        return array_map(
            fn(MeetingRoom $room) => new MeetingRoomListItem(
                meetingRoomId: $room->meetingRoomId()->value,
                name: $room->name()->value,
                capacity: $room->capacity()->value,
                equipments: array_map(fn(Equipment $e) => $e->label(), $room->equipments()),
            ),
            $meetingRooms,
        );
    }
}

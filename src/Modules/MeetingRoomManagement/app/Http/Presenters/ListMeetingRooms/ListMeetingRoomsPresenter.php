<?php

namespace Modules\MeetingRoomManagement\Http\Presenters\ListMeetingRooms;

use Modules\MeetingRoomManagement\UseCase\ListMeetingRooms\MeetingRoomListItem;

class ListMeetingRoomsPresenter
{
    /** @param MeetingRoomListItem[] $listItems */
    public function __construct(private array $listItems) {}

    /** @return list<array<string, mixed>> */
    public function getMeetingRooms(): array
    {
        return array_map(
            fn(MeetingRoomListItem $item) => [
                'meetingRoomId' => $item->meetingRoomId,
                'name'          => $item->name,
                'capacity'      => $item->capacity,
                'equipments'    => $item->equipments,
            ],
            $this->listItems,
        );
    }
}

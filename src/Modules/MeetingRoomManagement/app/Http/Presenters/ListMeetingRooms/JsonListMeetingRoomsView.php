<?php

namespace Modules\MeetingRoomManagement\Http\Presenters\ListMeetingRooms;

use Illuminate\Http\JsonResponse;

class JsonListMeetingRoomsView
{
    public function response(ListMeetingRoomsPresenter $presenter): JsonResponse
    {
        return response()->json(
            $this->buildResponseArray($presenter),
            200,
        );
    }

    /** @return array<string, mixed> */
    private function buildResponseArray(ListMeetingRoomsPresenter $presenter): array
    {
        return [
            'meetingRooms' => $presenter->getMeetingRooms(),
        ];
    }
}

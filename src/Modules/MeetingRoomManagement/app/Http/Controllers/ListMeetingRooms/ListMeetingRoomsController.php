<?php

namespace Modules\MeetingRoomManagement\Http\Controllers\ListMeetingRooms;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\MeetingRoomManagement\Http\Presenters\ListMeetingRooms\JsonListMeetingRoomsView;
use Modules\MeetingRoomManagement\Http\Presenters\ListMeetingRooms\ListMeetingRoomsPresenter;
use Modules\MeetingRoomManagement\UseCase\ListMeetingRooms\ListMeetingRoomsUseCase;

class ListMeetingRoomsController
{
    public function __construct(
        private ListMeetingRoomsUseCase $useCase,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $items = $this->useCase->execute();
        $presenter = new ListMeetingRoomsPresenter($items);
        return (new JsonListMeetingRoomsView())->response($presenter);
    }
}

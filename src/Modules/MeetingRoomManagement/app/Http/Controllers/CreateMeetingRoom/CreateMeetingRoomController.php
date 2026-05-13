<?php

namespace Modules\MeetingRoomManagement\Http\Controllers\CreateMeetingRoom;

use Illuminate\Http\Response;
use Modules\MeetingRoomManagement\UseCase\CreateMeetingRoom\CreateMeetingRoomInput;
use Modules\MeetingRoomManagement\UseCase\CreateMeetingRoom\CreateMeetingRoomUseCase;

class CreateMeetingRoomController
{
    public function __construct(
        private CreateMeetingRoomUseCase $useCase,
    ) {}

    public function __invoke(CreateMeetingRoomRequest $request): Response
    {
        $this->useCase->execute(new CreateMeetingRoomInput(
            name: $request->input('name'),
            capacity: (int) $request->input('capacity'),
            equipments: $request->input('equipments', []),
        ));

        return response()->noContent(Response::HTTP_CREATED);
    }
}

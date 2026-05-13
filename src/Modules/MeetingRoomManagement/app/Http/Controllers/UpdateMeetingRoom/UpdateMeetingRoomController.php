<?php

namespace Modules\MeetingRoomManagement\Http\Controllers\UpdateMeetingRoom;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomNotFoundException;
use Modules\MeetingRoomManagement\UseCase\UpdateMeetingRoom\UpdateMeetingRoomInput;
use Modules\MeetingRoomManagement\UseCase\UpdateMeetingRoom\UpdateMeetingRoomUseCase;

class UpdateMeetingRoomController
{
    public function __construct(
        private UpdateMeetingRoomUseCase $useCase,
    ) {}

    public function __invoke(UpdateMeetingRoomRequest $request, string $meetingRoomId): Response|JsonResponse
    {
        try {
            $this->useCase->execute(new UpdateMeetingRoomInput(
                meetingRoomId: $meetingRoomId,
                name: $request->input('name'),
                capacity: (int) $request->input('capacity'),
                equipments: $request->input('equipments', []),
            ));
        } catch (MeetingRoomNotFoundException) {
            return response()->json(['message' => '会議室が見つかりません。'], 404);
        }

        return response()->noContent();
    }
}

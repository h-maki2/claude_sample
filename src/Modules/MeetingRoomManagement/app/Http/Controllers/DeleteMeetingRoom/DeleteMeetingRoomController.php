<?php

namespace Modules\MeetingRoomManagement\Http\Controllers\DeleteMeetingRoom;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomHasActiveReservationsException;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomNotFoundException;
use Modules\MeetingRoomManagement\UseCase\DeleteMeetingRoom\DeleteMeetingRoomUseCase;

class DeleteMeetingRoomController
{
    public function __construct(
        private DeleteMeetingRoomUseCase $useCase,
    ) {}

    public function __invoke(string $meetingRoomId): Response|JsonResponse
    {
        try {
            $this->useCase->execute($meetingRoomId);
        } catch (MeetingRoomNotFoundException) {
            return response()->json(['message' => '会議室が見つかりません。'], 404);
        } catch (MeetingRoomHasActiveReservationsException) {
            return response()->json(['message' => '有効な予約が存在するため、会議室を削除できません。'], 409);
        }

        return response()->noContent();
    }
}

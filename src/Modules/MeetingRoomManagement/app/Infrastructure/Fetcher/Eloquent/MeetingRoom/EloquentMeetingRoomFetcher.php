<?php

namespace Modules\MeetingRoomManagement\Infrastructure\Fetcher\Eloquent\MeetingRoom;

use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomDTO;
use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomFetcher;
use Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom\MeetingRoomModel;

class EloquentMeetingRoomFetcher implements MeetingRoomFetcher
{
    public function fetchById(string $meetingRoomId): ?MeetingRoomDTO
    {
        $model = MeetingRoomModel::with('equipments')->find($meetingRoomId);

        if ($model === null) {
            return null;
        }

        return $this->toDTO($model);
    }

    /** @return MeetingRoomDTO[] */
    public function fetchAll(): array
    {
        return MeetingRoomModel::with('equipments')
            ->get()
            ->map(fn(MeetingRoomModel $model) => $this->toDTO($model))
            ->all();
    }

    private function toDTO(MeetingRoomModel $model): MeetingRoomDTO
    {
        return new MeetingRoomDTO(
            meetingRoomId: $model->meeting_room_id,
            name: $model->name,
            capacity: $model->capacity,
            equipments: $model->equipments->pluck('equipment')->all(),
        );
    }
}

<?php

namespace Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom;

use Illuminate\Support\Str;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoom;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;

class EloquentMeetingRoomRepository implements MeetingRoomRepository
{
    public function nextId(): MeetingRoomId
    {
        return new MeetingRoomId((string) Str::uuid7());
    }

    public function findById(MeetingRoomId $id): ?MeetingRoom
    {
        $model = MeetingRoomModel::with('equipments')->find($id->value);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(MeetingRoom $meetingRoom): void
    {
        MeetingRoomModel::updateOrCreate(
            ['meeting_room_id' => $meetingRoom->meetingRoomId()->value],
            [
                'name' => $meetingRoom->name()->value,
                'capacity' => $meetingRoom->capacity()->value,
            ],
        );

        MeetingRoomEquipmentModel::where('meeting_room_id', $meetingRoom->meetingRoomId()->value)->delete();

        foreach ($meetingRoom->equipments() as $equipment) {
            MeetingRoomEquipmentModel::create([
                'meeting_room_id' => $meetingRoom->meetingRoomId()->value,
                'equipment' => $equipment->value,
            ]);
        }
    }

    public function delete(MeetingRoomId $id): void
    {
        MeetingRoomModel::where('meeting_room_id', $id->value)->delete();
    }

    public function findAll(): array
    {
        return MeetingRoomModel::with('equipments')
            ->get()
            ->map(fn(MeetingRoomModel $model) => $this->toDomain($model))
            ->all();
    }

    private function toDomain(MeetingRoomModel $model): MeetingRoom
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, MeetingRoomEquipmentModel> $equipmentCollection */
        $equipmentCollection = $model->equipments;
        $equipments = $equipmentCollection
            ->map(fn(MeetingRoomEquipmentModel $e) => Equipment::from($e->equipment))
            ->all();

        return new MeetingRoom(
            new MeetingRoomId($model->meeting_room_id),
            new MeetingRoomName($model->name),
            new Capacity($model->capacity),
            $equipments,
        );
    }
}

<?php

namespace Tests\MeetingRoomManagement\Helpers\Infrastructure\Repository\InMemory;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoom;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\TestMeetingRoomIdFactory;

class InMemoryMeetingRoomRepository implements MeetingRoomRepository
{
    /** @var array<string, MeetingRoom> */
    private array $store = [];

    public function nextId(): MeetingRoomId
    {
        return TestMeetingRoomIdFactory::create();
    }

    public function findById(MeetingRoomId $id): ?MeetingRoom
    {
        return $this->store[$id->value] ?? null;
    }

    public function save(MeetingRoom $meetingRoom): void
    {
        $this->store[$meetingRoom->meetingRoomId()->value] = clone $meetingRoom;
    }

    public function delete(MeetingRoomId $id): void
    {
        unset($this->store[$id->value]);
    }

    public function findAll(): array
    {
        return array_values(array_map(fn(MeetingRoom $room) => clone $room, $this->store));
    }
}

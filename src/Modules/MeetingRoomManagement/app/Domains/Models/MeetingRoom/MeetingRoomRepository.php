<?php

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;

interface MeetingRoomRepository
{
    public function nextId(): MeetingRoomId;

    public function findById(MeetingRoomId $id): ?MeetingRoom;

    public function save(MeetingRoom $meetingRoom): void;

    public function delete(MeetingRoomId $id): void;

    /** @return MeetingRoom[] */
    public function findAll(): array;
}

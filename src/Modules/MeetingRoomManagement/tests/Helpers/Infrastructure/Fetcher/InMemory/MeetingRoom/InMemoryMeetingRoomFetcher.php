<?php

namespace Tests\MeetingRoomManagement\Helpers\Infrastructure\Fetcher\InMemory\MeetingRoom;

use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomDTO;
use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomFetcher;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataStore;

class InMemoryMeetingRoomFetcher implements MeetingRoomFetcher, MeetingRoomDtoTestDataStore
{
    /** @var array<string, MeetingRoomDTO> */
    private array $testData = [];

    public function store(MeetingRoomDTO $meetingRoomDto): void
    {
        $this->testData[$meetingRoomDto->meetingRoomId] = $meetingRoomDto;
    }

    public function fetchById(string $meetingRoomId): ?MeetingRoomDTO
    {
        return $this->testData[$meetingRoomId] ?? null;
    }

    /** @return MeetingRoomDTO[] */
    public function fetchAll(): array
    {
        return array_values($this->testData);
    }
}

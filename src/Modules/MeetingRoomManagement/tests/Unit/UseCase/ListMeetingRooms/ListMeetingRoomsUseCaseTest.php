<?php

namespace Tests\MeetingRoomManagement\Unit\UseCase\ListMeetingRooms;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use Modules\MeetingRoomManagement\UseCase\ListMeetingRooms\ListMeetingRoomsUseCase;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\MeetingRoomTestDataCreator;
use Tests\MeetingRoomManagement\Helpers\Infrastructure\Repository\InMemory\InMemoryMeetingRoomRepository;
use PHPUnit\Framework\TestCase;

class ListMeetingRoomsUseCaseTest extends TestCase
{
    private InMemoryMeetingRoomRepository $meetingRoomRepository;
    private MeetingRoomTestDataCreator $meetingRoomTestDataCreator;
    private ListMeetingRoomsUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->meetingRoomRepository = new InMemoryMeetingRoomRepository();
        $this->meetingRoomTestDataCreator = new MeetingRoomTestDataCreator($this->meetingRoomRepository);
        $this->useCase = new ListMeetingRoomsUseCase(
            meetingRoomRepository: $this->meetingRoomRepository,
        );
    }

    public function test_会議室が0件のとき空のリストが返る(): void
    {
        // Given: 会議室なし

        // When
        $result = $this->useCase->execute();

        // Then
        $this->assertEmpty($result);
    }

    public function test_会議室が複数件登録されているときすべての会議室が返る(): void
    {
        // Given
        $this->meetingRoomTestDataCreator->create(
            id: new MeetingRoomId('01957b3c-1234-7abc-8def-000000000001'),
            name: new MeetingRoomName('第1会議室'),
        );
        $this->meetingRoomTestDataCreator->create(
            id: new MeetingRoomId('01957b3c-1234-7abc-8def-000000000002'),
            name: new MeetingRoomName('第2会議室'),
        );

        // When
        $result = $this->useCase->execute();

        // Then
        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing(
            ['01957b3c-1234-7abc-8def-000000000001', '01957b3c-1234-7abc-8def-000000000002'],
            [$result[0]->meetingRoomId, $result[1]->meetingRoomId],
        );
    }

    public function test_返却されるMeetingRoomListItemのプロパティが正しく変換される(): void
    {
        // Given
        $id = '01957b3c-1234-7abc-8def-000000000001';
        $name = '大会議室';
        $capacity = 20;
        $equipments = [Equipment::WHITEBOARD, Equipment::PROJECTOR];

        $this->meetingRoomTestDataCreator->create(
            id: new MeetingRoomId($id),
            name: new MeetingRoomName($name),
            capacity: new Capacity($capacity),
            equipments: $equipments,
        );

        // When
        $result = $this->useCase->execute();

        // Then
        $this->assertCount(1, $result);
        $item = $result[0];
        $this->assertSame($id, $item->meetingRoomId);
        $this->assertSame($name, $item->name);
        $this->assertSame($capacity, $item->capacity);
        $this->assertSame(
            [Equipment::WHITEBOARD->label(), Equipment::PROJECTOR->label()],
            $item->equipments,
        );
    }
}

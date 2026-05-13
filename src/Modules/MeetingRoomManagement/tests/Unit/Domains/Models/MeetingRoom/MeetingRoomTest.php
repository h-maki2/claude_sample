<?php

namespace Tests\MeetingRoomManagement\Unit\Domains\Models\MeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoom;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use PHPUnit\Framework\TestCase;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\TestMeetingRoomFactory;

class MeetingRoomTest extends TestCase
{
    public function test_有効な値で会議室を生成できる(): void
    {
        // Given
        $id = new MeetingRoomId('01957b3c-1234-7abc-8def-000000000001');
        $name = new MeetingRoomName('第1会議室');
        $capacity = new Capacity(10);
        $equipments = [Equipment::WHITEBOARD, Equipment::PROJECTOR];

        // When
        $room = new MeetingRoom($id, $name, $capacity, $equipments);

        // Then
        $this->assertSame($id, $room->meetingRoomId());
        $this->assertSame($name, $room->name());
        $this->assertSame($capacity, $room->capacity());
        $this->assertSame($equipments, $room->equipments());
    }

    public function test_備品なし空配列で会議室を生成できる(): void
    {
        // When
        $room = TestMeetingRoomFactory::create(equipments: []);

        // Then
        $this->assertSame([], $room->equipments());
    }

    public function test_複数の備品を持つ会議室を生成できる(): void
    {
        // Given
        $equipments = [Equipment::WHITEBOARD, Equipment::PROJECTOR, Equipment::MONITOR, Equipment::VIDEO_CONFERENCE_SYSTEM];

        // When
        $room = TestMeetingRoomFactory::create(equipments: $equipments);

        // Then
        $this->assertSame($equipments, $room->equipments());
    }

    public function test_同じ備品を重複して指定した場合は例外が発生する(): void
    {
        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('備品は重複して指定できません。');
        TestMeetingRoomFactory::create(equipments: [Equipment::WHITEBOARD, Equipment::WHITEBOARD]);
    }

    public function test_会議室名と定員数と備品を変更できる(): void
    {
        // Given
        $room = TestMeetingRoomFactory::create(
            name: new MeetingRoomName('第1会議室'),
            capacity: new Capacity(10),
            equipments: [Equipment::WHITEBOARD],
        );
        $newName = new MeetingRoomName('第2会議室');
        $newCapacity = new Capacity(20);
        $newEquipments = [Equipment::PROJECTOR, Equipment::MONITOR];

        // When
        $room->update($newName, $newCapacity, $newEquipments);

        // Then
        $this->assertSame($newName, $room->name());
        $this->assertSame($newCapacity, $room->capacity());
        $this->assertSame($newEquipments, $room->equipments());
    }

    public function test_updateで備品を空にできる(): void
    {
        // Given
        $room = TestMeetingRoomFactory::create(equipments: [Equipment::WHITEBOARD]);

        // When
        $room->update($room->name(), $room->capacity(), []);

        // Then
        $this->assertSame([], $room->equipments());
    }

    public function test_updateで重複した備品を指定した場合は例外が発生する(): void
    {
        // Given
        $room = TestMeetingRoomFactory::create();

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('備品は重複して指定できません。');
        $room->update($room->name(), $room->capacity(), [Equipment::WHITEBOARD, Equipment::WHITEBOARD]);
    }
}

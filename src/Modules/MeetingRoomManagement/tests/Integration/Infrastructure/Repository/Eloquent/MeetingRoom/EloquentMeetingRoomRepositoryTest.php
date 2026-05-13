<?php

namespace Tests\MeetingRoomManagement\Integration\Infrastructure\Repository\Eloquent\MeetingRoom;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom\EloquentMeetingRoomRepository;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\MeetingRoomTestDataCreator;
use Tests\TestCase;

class EloquentMeetingRoomRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentMeetingRoomRepository $repository;
    private MeetingRoomTestDataCreator $creator;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(EloquentMeetingRoomRepository::class);
        $this->creator = new MeetingRoomTestDataCreator($this->repository);
    }

    public function test_備品なしで会議室を登録できる(): void
    {
        $name = '第1会議室';
        $capacity = 10;
        $room = $this->creator->create(
            name: new MeetingRoomName($name),
            capacity: new Capacity($capacity),
            equipments: [],
        );

        $saved = $this->repository->findById($room->meetingRoomId());

        $this->assertNotNull($saved);
        $this->assertSame($name, $saved->name()->value);
        $this->assertSame($capacity, $saved->capacity()->value);
        $this->assertSame([], $saved->equipments());
    }

    public function test_備品ありで会議室を登録できる(): void
    {
        $equipments = [Equipment::WHITEBOARD, Equipment::PROJECTOR];
        $room = $this->creator->create(equipments: $equipments);

        $saved = $this->repository->findById($room->meetingRoomId());

        $this->assertNotNull($saved);
        $this->assertEqualsCanonicalizing($equipments, $saved->equipments());
    }

    public function test_会議室を更新できる(): void
    {
        $room = $this->creator->create(
            name: new MeetingRoomName('旧会議室名'),
            capacity: new Capacity(5),
            equipments: [Equipment::WHITEBOARD],
        );

        $newName = '新会議室名';
        $newCapacity = 20;
        $newEquipments = [Equipment::PROJECTOR, Equipment::MONITOR];
        $room->update(
            new MeetingRoomName($newName),
            new Capacity($newCapacity),
            $newEquipments,
        );
        $this->repository->save($room);

        $updated = $this->repository->findById($room->meetingRoomId());

        $this->assertNotNull($updated);
        $this->assertSame($newName, $updated->name()->value);
        $this->assertSame($newCapacity, $updated->capacity()->value);
        $this->assertEqualsCanonicalizing($newEquipments, $updated->equipments());
    }

    public function test_IDで会議室を取得できる(): void
    {
        $room = $this->creator->create(name: new MeetingRoomName('取得テスト会議室'));

        $found = $this->repository->findById($room->meetingRoomId());

        $this->assertNotNull($found);
        $this->assertTrue($room->meetingRoomId()->equals($found->meetingRoomId()));
    }

    public function test_存在しないIDを指定した場合はnullを返す(): void
    {
        $nonExistentId = new MeetingRoomId('01957b3c-9999-7abc-8def-000000000099');

        $result = $this->repository->findById($nonExistentId);

        $this->assertNull($result);
    }

    public function test_会議室を削除できる(): void
    {
        $room = $this->creator->create();

        $this->repository->delete($room->meetingRoomId());

        $this->assertNull($this->repository->findById($room->meetingRoomId()));
    }

    public function test_削除後はfindByIdで取得できない(): void
    {
        $room = $this->creator->create(equipments: [Equipment::WHITEBOARD]);

        $this->repository->delete($room->meetingRoomId());

        $this->assertNull($this->repository->findById($room->meetingRoomId()));
    }

    public function test_すべての会議室を取得できる(): void
    {
        $room1 = $this->creator->create(name: new MeetingRoomName('会議室A'));
        $room2 = $this->creator->create(
            id: new MeetingRoomId('01957b3c-1234-7abc-8def-000000000002'),
            name: new MeetingRoomName('会議室B'),
        );

        $all = $this->repository->findAll();

        $this->assertCount(2, $all);
        $ids = array_map(fn($r) => $r->meetingRoomId()->value, $all);
        $this->assertEqualsCanonicalizing(
            [$room1->meetingRoomId()->value, $room2->meetingRoomId()->value],
            $ids,
        );
    }
}

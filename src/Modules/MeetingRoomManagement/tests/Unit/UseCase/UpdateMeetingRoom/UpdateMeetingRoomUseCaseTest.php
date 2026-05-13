<?php

namespace Tests\MeetingRoomManagement\Unit\UseCase\UpdateMeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomNotFoundException;
use Modules\MeetingRoomManagement\UseCase\UpdateMeetingRoom\UpdateMeetingRoomInput;
use Modules\MeetingRoomManagement\UseCase\UpdateMeetingRoom\UpdateMeetingRoomUseCase;
use Tests\Helpers\Infrastructure\Transaction\TestTransactionExecutor;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\TestMeetingRoomFactory;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\TestMeetingRoomIdFactory;
use Tests\MeetingRoomManagement\Helpers\Infrastructure\Repository\InMemory\InMemoryMeetingRoomRepository;
use PHPUnit\Framework\TestCase;

class UpdateMeetingRoomUseCaseTest extends TestCase
{
    private InMemoryMeetingRoomRepository $meetingRoomRepository;

    private UpdateMeetingRoomUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->meetingRoomRepository = new InMemoryMeetingRoomRepository();
        $this->useCase = new UpdateMeetingRoomUseCase(
            meetingRoomRepository: $this->meetingRoomRepository,
            transactionExecutor: new TestTransactionExecutor(),
        );
    }

    public function test_有効な入力で会議室を編集できる(): void
    {
        // Given: 更新前の値を明示して会議室を登録しておく
        $existingRoom = TestMeetingRoomFactory::create(
            name: new MeetingRoomName('第1会議室'),
            capacity: new Capacity(10),
            equipments: [],
        );
        $this->meetingRoomRepository->save($existingRoom);

        $newName = '大会議室';
        $newCapacity = 30;
        $ホワイトボード = Equipment::WHITEBOARD;
        $プロジェクター = Equipment::PROJECTOR;

        $input = new UpdateMeetingRoomInput(
            meetingRoomId: $existingRoom->meetingRoomId()->value,
            name: $newName,
            capacity: $newCapacity,
            equipments: [$ホワイトボード->value, $プロジェクター->value],
        );

        // When
        $this->useCase->execute($input);

        // Then: 値が更新前から変わっていることを検証する
        $updated = $this->meetingRoomRepository->findById($existingRoom->meetingRoomId());
        $this->assertNotNull($updated);
        $this->assertSame($newName, $updated->name()->value);
        $this->assertSame($newCapacity, $updated->capacity()->value);
        $this->assertContains($ホワイトボード, $updated->equipments());
        $this->assertContains($プロジェクター, $updated->equipments());
    }

    public function test_備品なしで会議室を編集できる(): void
    {
        // Given: 備品ありで登録し、更新で備品を空にする
        $existingRoom = TestMeetingRoomFactory::create(
            equipments: [Equipment::WHITEBOARD],
        );
        $this->meetingRoomRepository->save($existingRoom);

        $newName = '小会議室';
        $newCapacity = 4;

        $input = new UpdateMeetingRoomInput(
            meetingRoomId: $existingRoom->meetingRoomId()->value,
            name: $newName,
            capacity: $newCapacity,
            equipments: [],
        );

        // When
        $this->useCase->execute($input);

        // Then: 備品が空になっていることを検証する
        $updated = $this->meetingRoomRepository->findById($existingRoom->meetingRoomId());
        $this->assertNotNull($updated);
        $this->assertSame($newName, $updated->name()->value);
        $this->assertSame($newCapacity, $updated->capacity()->value);
        $this->assertEmpty($updated->equipments());
    }

    public function test_存在しない会議室IDを指定した場合は編集に失敗する(): void
    {
        // Given: リポジトリが空の状態（会議室が登録されていない）
        $input = new UpdateMeetingRoomInput(
            meetingRoomId: TestMeetingRoomIdFactory::create()->value,
            name: '第1会議室',
            capacity: 10,
            equipments: [],
        );

        // When / Then
        $this->expectException(MeetingRoomNotFoundException::class);
        $this->useCase->execute($input);
    }

}

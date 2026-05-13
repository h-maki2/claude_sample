<?php

namespace Tests\MeetingRoomManagement\Unit\UseCase\CreateMeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\UseCase\CreateMeetingRoom\CreateMeetingRoomInput;
use Modules\MeetingRoomManagement\UseCase\CreateMeetingRoom\CreateMeetingRoomUseCase;
use Tests\Helpers\Infrastructure\Transaction\TestTransactionExecutor;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\TestMeetingRoomIdFactory;
use Tests\MeetingRoomManagement\Helpers\Infrastructure\Repository\InMemory\InMemoryMeetingRoomRepository;
use PHPUnit\Framework\TestCase;

class CreateMeetingRoomUseCaseTest extends TestCase
{
    private InMemoryMeetingRoomRepository $meetingRoomRepository;

    private CreateMeetingRoomUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->meetingRoomRepository = new InMemoryMeetingRoomRepository();
        $this->useCase = new CreateMeetingRoomUseCase(
            meetingRoomRepository: $this->meetingRoomRepository,
            transactionExecutor: new TestTransactionExecutor(),
        );
    }

    public function test_有効な入力で会議室を登録できる(): void
    {
        // Given
        $testMeetingRoom = '第1会議室';
        $testCapacity = 10;
        $ホワイトボード = Equipment::WHITEBOARD;
        $プロジェクター = Equipment::PROJECTOR;

        $input = new CreateMeetingRoomInput(
            name: $testMeetingRoom,
            capacity: $testCapacity,
            equipments: [$ホワイトボード->value, $プロジェクター->value],
        );

        // When
        $this->useCase->execute($input);

        // Then
        $saved = $this->meetingRoomRepository->findById(TestMeetingRoomIdFactory::create());
        $this->assertNotNull($saved);
        $this->assertSame($testMeetingRoom, $saved->name()->value);
        $this->assertSame($testCapacity, $saved->capacity()->value);
        $this->assertContains($ホワイトボード, $saved->equipments());
        $this->assertContains($プロジェクター, $saved->equipments());
    }

    public function test_備品なしで会議室を登録できる(): void
    {
        // Given
        $input = new CreateMeetingRoomInput(
            name: '第2会議室',
            capacity: 4,
            equipments: [],
        );

        // When
        $this->useCase->execute($input);

        // Then
        $saved = $this->meetingRoomRepository->findById(TestMeetingRoomIdFactory::create());
        $this->assertNotNull($saved);
        $this->assertSame('第2会議室', $saved->name()->value);
        $this->assertSame(4, $saved->capacity()->value);
        $this->assertEmpty($saved->equipments());
    }

}

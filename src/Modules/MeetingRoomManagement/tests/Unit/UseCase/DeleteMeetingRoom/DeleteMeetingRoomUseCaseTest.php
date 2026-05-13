<?php

namespace Tests\MeetingRoomManagement\Unit\UseCase\DeleteMeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomHasActiveReservationsException;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomNotFoundException;
use Modules\MeetingRoomManagement\UseCase\DeleteMeetingRoom\DeleteMeetingRoomUseCase;
use Modules\ReservationManagement\Contracts\Reservation\ReservationExistenceChecker;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Helpers\Infrastructure\Transaction\TestTransactionExecutor;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\MeetingRoomTestDataCreator;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\TestMeetingRoomIdFactory;
use Tests\MeetingRoomManagement\Helpers\Infrastructure\Repository\InMemory\InMemoryMeetingRoomRepository;
use PHPUnit\Framework\TestCase;

class DeleteMeetingRoomUseCaseTest extends TestCase
{
    private InMemoryMeetingRoomRepository $meetingRoomRepository;
    private MeetingRoomTestDataCreator $meetingRoomTestDataCreator;
    private TestTransactionExecutor $transactionExecutor;
    private ReservationExistenceChecker&MockObject $reservationExistenceChecker;
    private DeleteMeetingRoomUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->meetingRoomRepository = new InMemoryMeetingRoomRepository();
        $this->meetingRoomTestDataCreator = new MeetingRoomTestDataCreator($this->meetingRoomRepository);
        $this->transactionExecutor = new TestTransactionExecutor();
        $this->reservationExistenceChecker = $this->createMock(ReservationExistenceChecker::class);
        $this->useCase = new DeleteMeetingRoomUseCase(
            meetingRoomRepository: $this->meetingRoomRepository,
            transactionExecutor: $this->transactionExecutor,
            reservationExistenceChecker: $this->reservationExistenceChecker,
        );
    }

    public function test_存在する会議室を削除できる(): void
    {
        // Given: 会議室が存在し、予約は存在しない
        $existingRoom = $this->meetingRoomTestDataCreator->create();
        $this->reservationExistenceChecker->method('hasActiveReservationsByMeetingRoomId')->willReturn(false);

        // When
        $this->useCase->execute($existingRoom->meetingRoomId()->value);

        // Then: リポジトリから削除されていること
        $deleted = $this->meetingRoomRepository->findById($existingRoom->meetingRoomId());
        $this->assertNull($deleted);
    }

    public function test_存在しない会議室IDを指定した場合は削除に失敗する(): void
    {
        // Given: リポジトリが空の状態（会議室が登録されていない）
        $nonExistentId = TestMeetingRoomIdFactory::create()->value;

        // When / Then
        $this->expectException(MeetingRoomNotFoundException::class);
        $this->useCase->execute($nonExistentId);
    }

    public function test_予約が存在する会議室は削除できない(): void
    {
        // Given: 会議室が存在し、アクティブな予約が存在する
        $existingRoom = $this->meetingRoomTestDataCreator->create();
        $this->reservationExistenceChecker->method('hasActiveReservationsByMeetingRoomId')->willReturn(true);

        // When / Then
        $this->expectException(MeetingRoomHasActiveReservationsException::class);
        $this->useCase->execute($existingRoom->meetingRoomId()->value);
    }
}

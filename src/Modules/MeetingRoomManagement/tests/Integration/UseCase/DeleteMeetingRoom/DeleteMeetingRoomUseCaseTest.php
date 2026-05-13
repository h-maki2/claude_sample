<?php

namespace Tests\MeetingRoomManagement\Integration\UseCase\DeleteMeetingRoom;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomHasActiveReservationsException;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomNotFoundException;
use Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom\EloquentMeetingRoomRepository;
use Modules\MeetingRoomManagement\UseCase\DeleteMeetingRoom\DeleteMeetingRoomUseCase;
use Modules\ReservationManagement\Contracts\Reservation\ReservationExistenceChecker;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\MeetingRoomTestDataCreator;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\TestMeetingRoomIdFactory;
use Tests\TestCase;

class DeleteMeetingRoomUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private EloquentMeetingRoomRepository $meetingRoomRepository;
    private MeetingRoomTestDataCreator $meetingRoomTestDataCreator;
    private DeleteMeetingRoomUseCase $useCase;
    private ReservationExistenceChecker&MockObject $reservationExistenceChecker;

    public function setUp(): void
    {
        parent::setUp();
        $this->meetingRoomRepository = app(EloquentMeetingRoomRepository::class);
        $this->meetingRoomTestDataCreator = new MeetingRoomTestDataCreator($this->meetingRoomRepository);
        $this->reservationExistenceChecker = $this->createMock(ReservationExistenceChecker::class);
        $this->app->instance(ReservationExistenceChecker::class, $this->reservationExistenceChecker);
        $this->useCase = app(DeleteMeetingRoomUseCase::class);
    }

    public function test_予約が存在しない会議室を削除できる(): void
    {
        // Given: 会議室が存在し、予約は存在しない
        $room = $this->meetingRoomTestDataCreator->create();

        // When
        $this->useCase->execute($room->meetingRoomId()->value);

        // Then: 会議室がリポジトリから削除されている
        $deleted = $this->meetingRoomRepository->findById($room->meetingRoomId());
        $this->assertNull($deleted);
    }

    public function test_予約が存在する会議室は削除できない(): void
    {
        // Given: 会議室が存在し、アクティブな予約が存在する
        $room = $this->meetingRoomTestDataCreator->create();
        $this->reservationExistenceChecker
            ->method('hasActiveReservationsByMeetingRoomId')
            ->willReturn(true);

        // When / Then
        $this->expectException(MeetingRoomHasActiveReservationsException::class);
        $this->useCase->execute($room->meetingRoomId()->value);
    }

    public function test_存在しない会議室は削除できない(): void
    {
        // Given: リポジトリが空の状態（会議室が登録されていない）
        $nonExistentId = TestMeetingRoomIdFactory::create()->value;

        // When / Then
        $this->expectException(MeetingRoomNotFoundException::class);
        $this->useCase->execute($nonExistentId);
    }
}

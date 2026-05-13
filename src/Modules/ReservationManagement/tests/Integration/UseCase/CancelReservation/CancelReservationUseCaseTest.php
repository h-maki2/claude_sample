<?php

namespace Tests\ReservationManagement\Integration\UseCase\CancelReservation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation\EloquentReservationRepository;
use Modules\ReservationManagement\UseCase\CancelReservation\CancelReservationUseCase;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationIdFactory;
use Tests\TestCase;

class CancelReservationUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';

    private EloquentReservationRepository $reservationRepository;
    private ReservationTestDataCreator $reservationTestDataCreator;
    private CancelReservationUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->reservationRepository = app(EloquentReservationRepository::class);
        $this->app->instance(EloquentReservationRepository::class, $this->reservationRepository);
        $this->reservationTestDataCreator = new ReservationTestDataCreator($this->reservationRepository);

        $meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
        $meetingRoomCreator->create(meetingRoomId: self::DEFAULT_MEETING_ROOM_ID);

        $this->useCase = app(CancelReservationUseCase::class);
    }

    public function test_予約をキャンセルできる(): void
    {
        // Given
        $reservationId = TestReservationIdFactory::create();
        $this->reservationTestDataCreator->create(id: $reservationId);

        // When
        $this->useCase->execute($reservationId->value);

        // Then
        $cancelled = $this->reservationRepository->findById($reservationId);
        $this->assertNotNull($cancelled);
        $this->assertSame(ReservationStatus::CANCELLED, $cancelled->status());
    }
}

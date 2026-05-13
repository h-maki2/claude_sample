<?php

namespace Tests\ReservationManagement\Feature;

use App\Domains\Models\Share\Clock\Clock;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\TestCase;

class CancelReservationTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';

    private ReservationRepository $reservationRepository;
    private ReservationTestDataCreator $reservationCreator;
    private MeetingRoomDtoTestDataCreator $meetingRoomCreator;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->instance(Clock::class, new TestFixedClock(new DateTimeImmutable('2026-05-08 09:00:00')));
        $this->reservationRepository = app(ReservationRepository::class);
        $this->reservationCreator = new ReservationTestDataCreator($this->reservationRepository);
        $this->meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
    }

    public function test_予約をキャンセルできる(): void
    {
        // Given
        $this->meetingRoomCreator->create(meetingRoomId: self::DEFAULT_MEETING_ROOM_ID);
        $reservation = $this->reservationCreator->create();

        // When
        $response = $this->deleteJson(
            '/api/v1/reservations/' . $reservation->reservationId()->value,
        );

        // Then
        $response->assertStatus(204);
        $cancelled = $this->reservationRepository->findById($reservation->reservationId());
        $this->assertNotNull($cancelled);
        $this->assertSame(ReservationStatus::CANCELLED, $cancelled->status());
    }
}

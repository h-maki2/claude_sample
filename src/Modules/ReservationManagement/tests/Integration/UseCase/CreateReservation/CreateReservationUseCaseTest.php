<?php

namespace Tests\ReservationManagement\Integration\UseCase\CreateReservation;

use App\Domains\Models\Share\Clock\Clock;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation\EloquentReservationRepository;
use Modules\ReservationManagement\UseCase\CreateReservation\CreateReservationInput;
use Modules\ReservationManagement\UseCase\CreateReservation\CreateReservationUseCase;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\TestCase;

class CreateReservationUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';

    private EloquentReservationRepository $reservationRepository;
    private CreateReservationUseCase $useCase;
    private MeetingRoomDtoTestDataCreator $meetingRoomCreator;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->instance(Clock::class, new TestFixedClock(new DateTimeImmutable('2026-05-06 09:00:00')));
        $this->reservationRepository = app(EloquentReservationRepository::class);
        $this->useCase = app(CreateReservationUseCase::class);
        $this->meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
    }

    public function test_予約を登録できる(): void
    {
        // Given
        $this->meetingRoomCreator->create(meetingRoomId: self::DEFAULT_MEETING_ROOM_ID);
        $meetingRoomId = self::DEFAULT_MEETING_ROOM_ID;
        $name = '第1回企画会議';
        $contactPerson = '鈴木一郎';
        $email = 'suzuki@example.com';
        $startAt = new DateTimeImmutable('2026-05-07 10:00:00');
        $endAt = new DateTimeImmutable('2026-05-07 11:00:00');

        $input = new CreateReservationInput(
            meetingRoomId: $meetingRoomId,
            name: $name,
            contactPerson: $contactPerson,
            email: $email,
            startAt: $startAt,
            endAt: $endAt,
        );

        // When
        $this->useCase->execute($input);

        // Then
        $reservations = $this->reservationRepository->findActiveByDate($startAt);
        $this->assertCount(1, $reservations);

        $saved = iterator_to_array($reservations)[0];
        $this->assertSame($name, $saved->name()->value);
        $this->assertSame($contactPerson, $saved->contactPerson()->value);
        $this->assertSame($email, $saved->email()->value);
        $this->assertSame($meetingRoomId, $saved->meetingRoomId()->value);
        $this->assertEquals($startAt, $saved->timeRange()->startAt);
        $this->assertEquals($endAt, $saved->timeRange()->endAt);
        $this->assertSame(ReservationStatus::CONFIRMED, $saved->status());
    }
}

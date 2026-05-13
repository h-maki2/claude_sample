<?php

namespace Tests\ReservationManagement\Integration\UseCase\ChangeReservation;

use App\Domains\Models\Share\Clock\Clock;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation\EloquentReservationRepository;
use Modules\ReservationManagement\UseCase\ChangeReservation\ChangeReservationInput;
use Modules\ReservationManagement\UseCase\ChangeReservation\ChangeReservationUseCase;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationIdFactory;
use Tests\TestCase;

class ChangeReservationUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';

    private EloquentReservationRepository $reservationRepository;
    private ReservationTestDataCreator $reservationTestDataCreator;
    private ChangeReservationUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->reservationRepository = app(EloquentReservationRepository::class);
        $this->reservationTestDataCreator = new ReservationTestDataCreator($this->reservationRepository);

        $meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
        $meetingRoomCreator->create(meetingRoomId: self::DEFAULT_MEETING_ROOM_ID);

        $this->app->instance(Clock::class, new TestFixedClock(new DateTimeImmutable('2026-05-01 09:00:00')));
        $this->useCase = app(ChangeReservationUseCase::class);
    }

    public function test_有効な入力で予約をDBに変更できる(): void
    {
        // Given
        $reservationId = TestReservationIdFactory::create();
        $this->reservationTestDataCreator->create(
            id: $reservationId,
            meetingRoomId: new MeetingRoomId(self::DEFAULT_MEETING_ROOM_ID),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-05-01 10:00:00'),
                new DateTimeImmutable('2026-05-01 11:00:00'),
            ),
        );
        $newName = '変更後会議名';
        $newStartAt = new DateTimeImmutable('2026-05-01 14:00:00');
        $newEndAt = new DateTimeImmutable('2026-05-01 15:00:00');
        $input = new ChangeReservationInput(
            reservationId: $reservationId->value,
            name: $newName,
            startAt: $newStartAt,
            endAt: $newEndAt,
        );

        // When
        $this->useCase->execute($input);

        // Then
        $changed = $this->reservationRepository->findById($reservationId);
        $this->assertNotNull($changed);
        $this->assertSame($newName, $changed->name()->value);
        $this->assertEquals($newStartAt, $changed->timeRange()->startAt);
        $this->assertEquals($newEndAt, $changed->timeRange()->endAt);
        $this->assertSame(ReservationStatus::CONFIRMED, $changed->status());
    }
}

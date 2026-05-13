<?php

namespace Tests\ReservationManagement\Integration\Infrastructure\Reservation;

use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation\EloquentReservationRepository;
use Modules\ReservationManagement\Infrastructure\Reservation\EloquentReservationExistenceChecker;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\TestCase;

class EloquentReservationExistenceCheckerTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';
    private const ANOTHER_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000088';

    private EloquentReservationExistenceChecker $checker;
    private ReservationTestDataCreator $creator;

    private MeetingRoomDtoTestDataCreator $meetingRoomCreator;

    public function setUp(): void
    {
        parent::setUp();
        $this->checker = app(EloquentReservationExistenceChecker::class);
        $this->creator = new ReservationTestDataCreator(app(EloquentReservationRepository::class));

        $this->meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
        $this->meetingRoomCreator->create(meetingRoomId: self::DEFAULT_MEETING_ROOM_ID);
    }

    public function test_将来の確定済み予約がある場合はtrueを返す(): void
    {
        // Given
        $meetingRoomId = self::DEFAULT_MEETING_ROOM_ID;
        $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000001'),
            meetingRoomId: new MeetingRoomId($meetingRoomId),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2099-06-01 09:00:00'),
                new DateTimeImmutable('2099-06-01 10:00:00'),
            ),
            status: ReservationStatus::CONFIRMED,
        );

        // When
        $result = $this->checker->hasActiveReservationsByMeetingRoomId($meetingRoomId);

        // Then
        $this->assertTrue($result);
    }

    public function test_確定済み予約が存在しない場合はfalseを返す(): void
    {
        // Given: 予約なし

        // When
        $result = $this->checker->hasActiveReservationsByMeetingRoomId(self::DEFAULT_MEETING_ROOM_ID);

        // Then
        $this->assertFalse($result);
    }

    public function test_キャンセル済みの予約のみの場合はfalseを返す(): void
    {
        // Given
        $meetingRoomId = self::DEFAULT_MEETING_ROOM_ID;
        $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000011'),
            meetingRoomId: new MeetingRoomId($meetingRoomId),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2099-06-01 09:00:00'),
                new DateTimeImmutable('2099-06-01 10:00:00'),
            ),
            status: ReservationStatus::CANCELLED,
        );

        // When
        $result = $this->checker->hasActiveReservationsByMeetingRoomId($meetingRoomId);

        // Then
        $this->assertFalse($result);
    }

    public function test_終了済みの確定済み予約のみの場合はfalseを返す(): void
    {
        // Given
        $meetingRoomId = self::DEFAULT_MEETING_ROOM_ID;
        $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000021'),
            meetingRoomId: new MeetingRoomId($meetingRoomId),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2000-06-01 09:00:00'),
                new DateTimeImmutable('2000-06-01 10:00:00'),
            ),
            status: ReservationStatus::CONFIRMED,
        );

        // When
        $result = $this->checker->hasActiveReservationsByMeetingRoomId($meetingRoomId);

        // Then
        $this->assertFalse($result);
    }

    public function test_異なる会議室の確定済み予約のみの場合はfalseを返す(): void
    {
        // Given
        $this->meetingRoomCreator->create(meetingRoomId: self::ANOTHER_MEETING_ROOM_ID);

        $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000031'),
            meetingRoomId: new MeetingRoomId(self::ANOTHER_MEETING_ROOM_ID),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2099-06-01 09:00:00'),
                new DateTimeImmutable('2099-06-01 10:00:00'),
            ),
            status: ReservationStatus::CONFIRMED,
        );

        // When
        $result = $this->checker->hasActiveReservationsByMeetingRoomId(self::DEFAULT_MEETING_ROOM_ID);

        // Then
        $this->assertFalse($result);
    }
}

<?php

namespace Tests\ReservationManagement\Integration\Infrastructure\Repository\Eloquent\Reservation;

use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactEmail;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactPersonName;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationList;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation\EloquentReservationRepository;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\Eloquent\EloquentMeetingRoomDtoTestDataStore;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationFactory;
use Tests\TestCase;

class EloquentReservationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';
    private const ANOTHER_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000088';

    private EloquentReservationRepository $repository;
    private ReservationTestDataCreator $creator;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(EloquentReservationRepository::class);
        $this->creator = new ReservationTestDataCreator($this->repository);

        $meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
        $meetingRoomCreator->create(meetingRoomId: self::DEFAULT_MEETING_ROOM_ID);
    }

    public function test_予約を登録できる(): void
    {
        // Given
        $name = '第1回企画会議';
        $contactPerson = '鈴木一郎';
        $email = 'suzuki@example.com';
        $meetingRoomId = new MeetingRoomId(self::DEFAULT_MEETING_ROOM_ID);
        $timeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-06-01 09:00:00'),
            new DateTimeImmutable('2026-06-01 10:00:00'),
        );
        $reservation = TestReservationFactory::create(
            meetingRoomId: $meetingRoomId,
            name: new ReservationName($name),
            contactPerson: new ContactPersonName($contactPerson),
            email: new ContactEmail($email),
            timeRange: $timeRange,
        );

        // When
        $this->repository->save($reservation);

        // Then
        $saved = $this->repository->findById($reservation->reservationId());
        $this->assertNotNull($saved);
        $this->assertSame($name, $saved->name()->value);
        $this->assertSame($contactPerson, $saved->contactPerson()->value);
        $this->assertSame($email, $saved->email()->value);
        $this->assertSame($meetingRoomId->value, $saved->meetingRoomId()->value);
        $this->assertTrue($timeRange->equals($saved->timeRange()));
        $this->assertSame(ReservationStatus::CONFIRMED, $saved->status());
    }

    public function test_存在しないIDを指定した場合はnullを返す(): void
    {
        // Given
        $nonExistentId = new ReservationId('01957b3c-9999-7abc-8def-000000000099');

        // When
        $result = $this->repository->findById($nonExistentId);

        // Then
        $this->assertNull($result);
    }

    public function test_指定日の確定済み予約を取得できる(): void
    {
        // Given
        $targetDate = new DateTimeImmutable('2026-06-10');
        $検索対象の予約1 = $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000001'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-06-10 10:00:00'),
                new DateTimeImmutable('2026-06-10 11:00:00'),
            ),
        );
        $検索対象の予約2 = $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000002'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-06-10 14:00:00'),
                new DateTimeImmutable('2026-06-10 15:00:00'),
            ),
        );

        // When
        $results = $this->repository->findActiveByDate($targetDate);

        // Then
        $this->assertCount(2, $results);
        $this->assertEquals(
            new ReservationList([
                $検索対象の予約1,
                $検索対象の予約2,
            ]),
            $results
        );
    }

    public function test_キャンセル済み予約は取得できない(): void
    {
        // Given
        $targetDate = new DateTimeImmutable('2026-06-15');
        $検索対象の予約 = $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000001'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-06-15 10:00:00'),
                new DateTimeImmutable('2026-06-15 11:00:00'),
            ),
            status: ReservationStatus::CONFIRMED,
        );
        $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000002'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-06-15 13:00:00'),
                new DateTimeImmutable('2026-06-15 14:00:00'),
            ),
            status: ReservationStatus::CANCELLED,
        );

        // When
        $results = $this->repository->findActiveByDate($targetDate);

        // Then
        $this->assertCount(1, $results);
        $this->assertEquals(
            new ReservationList([
                $検索対象の予約,
            ]),
            $results
        );
    }

    public function test_会議室IDと日付で確定済み予約を取得できる(): void
    {
        // Given
        $roomId = new MeetingRoomId(self::DEFAULT_MEETING_ROOM_ID);
        $date = new DateTimeImmutable('2026-07-01');
        $予約A = $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000020'),
            meetingRoomId: $roomId,
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-07-01 09:00:00'),
                new DateTimeImmutable('2026-07-01 10:00:00'),
            ),
        );
        $予約B = $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000021'),
            meetingRoomId: $roomId,
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-07-01 14:00:00'),
                new DateTimeImmutable('2026-07-01 15:00:00'),
            ),
        );

        // When
        $results = $this->repository->findActiveByMeetingRoomIdAndDate($roomId, $date);

        // Then
        $this->assertCount(2, $results);
        $this->assertEquals(new ReservationList([$予約A, $予約B]), $results);
    }

    public function test_異なる会議室IDの予約はfindActiveByMeetingRoomIdAndDateで取得されない(): void
    {
        // Given
        $meetingRoomCreator = new MeetingRoomDtoTestDataCreator(new EloquentMeetingRoomDtoTestDataStore());
        $meetingRoomCreator->create(meetingRoomId: self::ANOTHER_MEETING_ROOM_ID);

        $room1 = new MeetingRoomId(self::DEFAULT_MEETING_ROOM_ID);
        $room2 = new MeetingRoomId(self::ANOTHER_MEETING_ROOM_ID);
        $date = new DateTimeImmutable('2026-07-02');
        $room1の予約 = $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000022'),
            meetingRoomId: $room1,
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-07-02 10:00:00'),
                new DateTimeImmutable('2026-07-02 11:00:00'),
            ),
        );
        $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000023'),
            meetingRoomId: $room2,
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-07-02 10:00:00'),
                new DateTimeImmutable('2026-07-02 11:00:00'),
            ),
        );

        // When
        $results = $this->repository->findActiveByMeetingRoomIdAndDate($room1, $date);

        // Then
        $this->assertCount(1, $results);
        $this->assertEquals(new ReservationList([$room1の予約]), $results);
    }

    public function test_キャンセル済みの予約はfindActiveByMeetingRoomIdAndDateで取得されない(): void
    {
        // Given
        $roomId = new MeetingRoomId(self::DEFAULT_MEETING_ROOM_ID);
        $date = new DateTimeImmutable('2026-07-03');
        $確定済み予約 = $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000024'),
            meetingRoomId: $roomId,
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-07-03 10:00:00'),
                new DateTimeImmutable('2026-07-03 11:00:00'),
            ),
            status: ReservationStatus::CONFIRMED,
        );
        $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000025'),
            meetingRoomId: $roomId,
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-07-03 13:00:00'),
                new DateTimeImmutable('2026-07-03 14:00:00'),
            ),
            status: ReservationStatus::CANCELLED,
        );

        // When
        $results = $this->repository->findActiveByMeetingRoomIdAndDate($roomId, $date);

        // Then
        $this->assertCount(1, $results);
        $this->assertEquals(new ReservationList([$確定済み予約]), $results);
    }

    public function test_異なる日付の予約は取得されない(): void
    {
        // Given
        $targetDate = new DateTimeImmutable('2026-06-20');
        $検索対象の予約 = $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000001'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-06-20 10:00:00'),
                new DateTimeImmutable('2026-06-20 11:00:00'),
            ),
        );
        $this->creator->create(
            id: new ReservationId('01957b3c-aaaa-7abc-8def-000000000002'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-06-21 10:00:00'),
                new DateTimeImmutable('2026-06-21 11:00:00'),
            ),
        );

        // When
        $results = $this->repository->findActiveByDate($targetDate);

        // Then
        $this->assertCount(1, $results);
        $this->assertEquals(
            new ReservationList([
                $検索対象の予約,
            ]),
            $results
        );
    }
}

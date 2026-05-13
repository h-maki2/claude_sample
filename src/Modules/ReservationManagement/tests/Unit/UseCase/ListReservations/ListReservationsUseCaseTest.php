<?php

namespace Tests\ReservationManagement\Unit\UseCase\ListReservations;

use DateTimeImmutable;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use Modules\ReservationManagement\UseCase\ListReservations\ListReservationsUseCase;
use PHPUnit\Framework\TestCase;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\MeetingRoomManagement\Helpers\Infrastructure\Fetcher\InMemory\MeetingRoom\InMemoryMeetingRoomFetcher;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\ReservationManagement\Helpers\Infrastructure\Repository\InMemory\InMemoryReservationRepository;

class ListReservationsUseCaseTest extends TestCase
{
    private InMemoryReservationRepository $reservationRepository;
    private ReservationTestDataCreator $reservationTestDataCreator;
    private InMemoryMeetingRoomFetcher $meetingRoomFetcher;
    private MeetingRoomDtoTestDataCreator $meetingRoomDtoTestDataCreator;
    private ListReservationsUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->reservationRepository = new InMemoryReservationRepository();
        $this->reservationTestDataCreator = new ReservationTestDataCreator($this->reservationRepository);
        $this->meetingRoomFetcher = new InMemoryMeetingRoomFetcher();
        $this->meetingRoomDtoTestDataCreator = new MeetingRoomDtoTestDataCreator($this->meetingRoomFetcher);
        $this->useCase = new ListReservationsUseCase(
            reservationRepository: $this->reservationRepository,
            meetingRoomFetcher: $this->meetingRoomFetcher,
        );
    }

    public function test_指定日付の予約一覧を会議室名付きで取得できる(): void
    {
        // Given
        $date = new DateTimeImmutable('2026-05-01');
        $meetingRoomId = '01957b3c-1234-7abc-8def-000000000099';
        $meetingRoomName = '第1会議室';
        $startAt = new DateTimeImmutable('2026-05-01 10:00:00');
        $endAt = new DateTimeImmutable('2026-05-01 11:00:00');

        $this->reservationTestDataCreator->create(
            meetingRoomId: new MeetingRoomId($meetingRoomId),
            timeRange: new ReservationTimeRange($startAt, $endAt),
        );

        $this->meetingRoomDtoTestDataCreator->create(
            meetingRoomId: $meetingRoomId,
            name: $meetingRoomName,
        );

        // When
        $result = $this->useCase->execute($date);

        // Then
        $this->assertCount(1, $result);
        $this->assertSame($meetingRoomId, $result[0]->meetingRoomId);
        $this->assertSame($meetingRoomName, $result[0]->meetingRoomName);
        $this->assertSame($startAt, $result[0]->startAt);
        $this->assertSame($endAt, $result[0]->endAt);
        $this->assertSame(ReservationStatus::CONFIRMED->name, $result[0]->status);
    }

    public function test_指定日付に予約が存在しない場合は空の配列を返す(): void
    {
        // Given
        $date = new DateTimeImmutable('2026-05-01');

        // When
        $result = $this->useCase->execute($date);

        // Then
        $this->assertSame([], $result);
    }

    public function test_キャンセル済みの予約は一覧に含まれない(): void
    {
        // Given
        $date = new DateTimeImmutable('2026-05-01');

        $this->reservationTestDataCreator->create(
            meetingRoomId: new MeetingRoomId('01957b3c-1234-7abc-8def-000000000099'),
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-05-01 10:00:00'),
                new DateTimeImmutable('2026-05-01 11:00:00'),
            ),
            status: ReservationStatus::CANCELLED,
        );

        // When
        $result = $this->useCase->execute($date);

        // Then
        $this->assertSame([], $result);
    }
}

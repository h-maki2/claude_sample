<?php

namespace Tests\ReservationManagement\Unit\UseCase\CreateReservation;

use DateTimeImmutable;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\UseCase\CreateReservation\CreateReservationInput;
use Modules\ReservationManagement\UseCase\CreateReservation\CreateReservationUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;
use Tests\Helpers\Infrastructure\Transaction\TestTransactionExecutor;
use Tests\MeetingRoomManagement\Helpers\Contracts\MeetingRoom\MeetingRoomDtoTestDataCreator;
use Tests\MeetingRoomManagement\Helpers\Infrastructure\Fetcher\InMemory\MeetingRoom\InMemoryMeetingRoomFetcher;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationIdFactory;
use Tests\ReservationManagement\Helpers\Infrastructure\Repository\InMemory\InMemoryReservationRepository;

class CreateReservationUseCaseTest extends TestCase
{
    private const DEFAULT_MEETING_ROOM_ID = '01957b3c-1234-7abc-8def-000000000099';

    private InMemoryReservationRepository $reservationRepository;
    private InMemoryMeetingRoomFetcher $meetingRoomFetcher;
    private MeetingRoomDtoTestDataCreator $meetingRoomDtoTestDataCreator;
    private TestFixedClock $clock;
    private CreateReservationUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->reservationRepository = new InMemoryReservationRepository();
        $this->meetingRoomFetcher = new InMemoryMeetingRoomFetcher();
        $this->meetingRoomDtoTestDataCreator = new MeetingRoomDtoTestDataCreator($this->meetingRoomFetcher);
        $this->clock = new TestFixedClock(new DateTimeImmutable('2026-05-06 09:00:00'));
        $this->useCase = new CreateReservationUseCase(
            reservationRepository: $this->reservationRepository,
            meetingRoomFetcher: $this->meetingRoomFetcher,
            transactionExecutor: new TestTransactionExecutor(),
            clock: $this->clock,
        );
    }

    public function test_有効な入力で予約を登録できる(): void
    {
        // Given
        $meetingRoomId = self::DEFAULT_MEETING_ROOM_ID;
        $name = '第1回企画会議';
        $contactPerson = '鈴木一郎';
        $email = 'suzuki@example.com';
        $startAt = new DateTimeImmutable('2026-05-07 10:00:00');
        $endAt = new DateTimeImmutable('2026-05-07 11:00:00');

        $this->meetingRoomDtoTestDataCreator->create(meetingRoomId: $meetingRoomId);

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
        $saved = $this->reservationRepository->findById(TestReservationIdFactory::create());
        $this->assertNotNull($saved);
        $this->assertSame($name, $saved->name()->value);
        $this->assertSame($contactPerson, $saved->contactPerson()->value);
        $this->assertSame($email, $saved->email()->value);
        $this->assertSame($meetingRoomId, $saved->meetingRoomId()->value);
        $this->assertEquals($startAt, $saved->timeRange()->startAt);
        $this->assertEquals($endAt, $saved->timeRange()->endAt);
        $this->assertSame(ReservationStatus::CONFIRMED, $saved->status());
    }

    public function test_存在しない会議室IDを指定した場合は例外が発生する(): void
    {
        // Given
        $input = new CreateReservationInput(
            meetingRoomId: '01957b3c-9999-7abc-8def-000000000000',
            name: '第1回企画会議',
            contactPerson: '鈴木一郎',
            email: 'suzuki@example.com',
            startAt: new DateTimeImmutable('2026-05-07 10:00:00'),
            endAt: new DateTimeImmutable('2026-05-07 11:00:00'),
        );

        // When / Then
        $this->expectException(\DomainException::class);
        $this->useCase->execute($input);
    }
}

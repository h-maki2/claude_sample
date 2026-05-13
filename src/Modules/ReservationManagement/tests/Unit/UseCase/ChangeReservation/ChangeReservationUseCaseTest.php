<?php

namespace Tests\ReservationManagement\Unit\UseCase\ChangeReservation;

use DateTimeImmutable;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use Modules\ReservationManagement\UseCase\ChangeReservation\ChangeReservationInput;
use Modules\ReservationManagement\UseCase\ChangeReservation\ChangeReservationUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;
use Tests\Helpers\Infrastructure\Transaction\TestTransactionExecutor;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationIdFactory;
use Tests\ReservationManagement\Helpers\Infrastructure\Repository\InMemory\InMemoryReservationRepository;

class ChangeReservationUseCaseTest extends TestCase
{
    private InMemoryReservationRepository $reservationRepository;
    private ReservationTestDataCreator $reservationTestDataCreator;
    private TestFixedClock $clock;
    private ChangeReservationUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->reservationRepository = new InMemoryReservationRepository();
        $this->reservationTestDataCreator = new ReservationTestDataCreator($this->reservationRepository);
        $this->clock = new TestFixedClock(new DateTimeImmutable('2026-05-01 09:00:00'));
        $this->useCase = new ChangeReservationUseCase(
            reservationRepository: $this->reservationRepository,
            transactionExecutor: new TestTransactionExecutor(),
            clock: $this->clock,
        );
    }

    public function test_有効な入力で予約を変更できる(): void
    {
        // Given
        $reservationId = TestReservationIdFactory::create();
        $meetingRoomId = new MeetingRoomId('01957b3c-1234-7abc-8def-000000000099');
        $this->reservationTestDataCreator->create(
            id: $reservationId,
            meetingRoomId: $meetingRoomId,
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

    public function test_存在しない予約IDを指定した場合は例外が発生する(): void
    {
        // Given
        $input = new ChangeReservationInput(
            reservationId: '01957b3c-1234-7abc-8def-000000000999',
            name: '変更後会議名',
            startAt: new DateTimeImmutable('2026-05-01 14:00:00'),
            endAt: new DateTimeImmutable('2026-05-01 15:00:00'),
        );

        // When / Then
        $this->expectException(\DomainException::class);
        $this->useCase->execute($input);
    }
}

<?php

namespace Tests\ReservationManagement\Unit\UseCase\CancelReservation;

use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\UseCase\CancelReservation\CancelReservationUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Infrastructure\Transaction\TestTransactionExecutor;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\ReservationTestDataCreator;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationIdFactory;
use Tests\ReservationManagement\Helpers\Infrastructure\Repository\InMemory\InMemoryReservationRepository;

class CancelReservationUseCaseTest extends TestCase
{
    private InMemoryReservationRepository $reservationRepository;
    private ReservationTestDataCreator $reservationTestDataCreator;
    private CancelReservationUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();
        $this->reservationRepository = new InMemoryReservationRepository();
        $this->reservationTestDataCreator = new ReservationTestDataCreator($this->reservationRepository);
        $this->useCase = new CancelReservationUseCase(
            reservationRepository: $this->reservationRepository,
            transactionExecutor: new TestTransactionExecutor(),
        );
    }

    public function test_有効な予約IDでキャンセルできる(): void
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

    public function test_存在しない予約IDを指定した場合は例外が発生する(): void
    {
        // Given
        $nonExistentId = '01957b3c-1234-7abc-8def-000000000999';

        // When / Then
        $this->expectException(\DomainException::class);
        $this->useCase->execute($nonExistentId);
    }
}

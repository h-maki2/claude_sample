<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use DateTimeImmutable;
use Modules\ReservationManagement\Domains\Models\Reservation\BufferTime;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationList;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use PHPUnit\Framework\TestCase;
use Tests\ReservationManagement\Helpers\Domains\Models\Reservation\TestReservationFactory;

class BufferTimeTest extends TestCase
{
    private BufferTime $bufferTime;

    public function setUp(): void
    {
        parent::setUp();
        $this->bufferTime = new BufferTime();
    }

    public function test_前の予約終了から11分空いている場合はバッファタイムを満たす(): void
    {
        // Given
        $preceding = TestReservationFactory::create(
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-04-30 09:00:00'),
                new DateTimeImmutable('2026-04-30 10:00:00'),
            ),
        );
        $reservationList = new ReservationList([$preceding]);
        $following = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 10:11:00'),
            new DateTimeImmutable('2026-04-30 11:00:00'),
        );

        // When
        $result = $this->bufferTime->isSatisfiedBetween($reservationList, $following);

        // Then
        $this->assertTrue($result);
    }

    public function test_前の予約終了からちょうど10分空いている場合はバッファタイムを満たす(): void
    {
        // Given
        $preceding = TestReservationFactory::create(
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-04-30 09:00:00'),
                new DateTimeImmutable('2026-04-30 10:00:00'),
            ),
        );
        $reservationList = new ReservationList([$preceding]);
        $following = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 10:10:00'),
            new DateTimeImmutable('2026-04-30 11:00:00'),
        );

        // When
        $result = $this->bufferTime->isSatisfiedBetween($reservationList, $following);

        // Then
        $this->assertTrue($result);
    }

    public function test_前の予約終了から9分しか空いていない場合はバッファタイムを満たさない(): void
    {
        // Given
        $preceding = TestReservationFactory::create(
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-04-30 09:00:00'),
                new DateTimeImmutable('2026-04-30 10:00:00'),
            ),
        );
        $reservationList = new ReservationList([$preceding]);
        $following = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 10:09:00'),
            new DateTimeImmutable('2026-04-30 11:00:00'),
        );

        // When
        $result = $this->bufferTime->isSatisfiedBetween($reservationList, $following);

        // Then
        $this->assertFalse($result);
    }

    public function test_前の予約終了直後の場合はバッファタイムを満たさない(): void
    {
        // Given
        $preceding = TestReservationFactory::create(
            timeRange: new ReservationTimeRange(
                new DateTimeImmutable('2026-04-30 09:00:00'),
                new DateTimeImmutable('2026-04-30 10:00:00'),
            ),
        );
        $reservationList = new ReservationList([$preceding]);
        $following = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 10:00:00'),
            new DateTimeImmutable('2026-04-30 11:00:00'),
        );

        // When
        $result = $this->bufferTime->isSatisfiedBetween($reservationList, $following);

        // Then
        $this->assertFalse($result);
    }

    public function test_前の予約が存在しない場合はバッファタイムを満たす(): void
    {
        // Given
        $reservationList = new ReservationList([]);
        $following = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 10:00:00'),
            new DateTimeImmutable('2026-04-30 11:00:00'),
        );

        // When
        $result = $this->bufferTime->isSatisfiedBetween($reservationList, $following);

        // Then
        $this->assertTrue($result);
    }
}

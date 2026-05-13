<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use DateTimeImmutable;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservablePeriod;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use PHPUnit\Framework\TestCase;
use Tests\Helpers\Domains\Models\Share\Clock\TestFixedClock;

class ReservablePeriodTest extends TestCase
{
    private TestFixedClock $clock;
    private ReservablePeriod $period;

    public function setUp(): void
    {
        parent::setUp();
        $this->clock = new TestFixedClock(new DateTimeImmutable('2026-04-30 10:00:00'));
        $this->period = new ReservablePeriod();
    }

    public function test_当日の予約は受付可能期間内である(): void
    {
        // Given
        $timeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 10:00:00'),
            new DateTimeImmutable('2026-04-30 11:00:00'),
        );

        // When
        $result = $this->period->isSatisfiedBy($timeRange, $this->clock);

        // Then
        $this->assertTrue($result);
    }

    public function test_14日後の予約は受付可能期間内である(): void
    {
        // Given
        $timeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-05-14 10:00:00'),
            new DateTimeImmutable('2026-05-14 11:00:00'),
        );

        // When
        $result = $this->period->isSatisfiedBy($timeRange, $this->clock);

        // Then
        $this->assertTrue($result);
    }

    public function test_15日後の予約は受付可能期間外である(): void
    {
        // Given
        $timeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-05-15 10:00:00'),
            new DateTimeImmutable('2026-05-15 11:00:00'),
        );

        // When
        $result = $this->period->isSatisfiedBy($timeRange, $this->clock);

        // Then
        $this->assertFalse($result);
    }

    public function test_昨日の予約は受付可能期間外である(): void
    {
        // Given
        $timeRange = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-29 10:00:00'),
            new DateTimeImmutable('2026-04-29 11:00:00'),
        );

        // When
        $result = $this->period->isSatisfiedBy($timeRange, $this->clock);

        // Then
        $this->assertFalse($result);
    }
}

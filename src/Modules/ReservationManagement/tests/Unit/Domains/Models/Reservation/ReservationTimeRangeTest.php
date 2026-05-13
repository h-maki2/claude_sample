<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use DateTimeImmutable;
use InvalidArgumentException;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;
use PHPUnit\Framework\TestCase;

class ReservationTimeRangeTest extends TestCase
{
    public function test_有効な時刻範囲でインスタンス化できる(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 09:00:00');
        $endAt = new DateTimeImmutable('2026-04-30 10:00:00');

        // When
        $range = new ReservationTimeRange($startAt, $endAt);

        // Then
        $this->assertSame($startAt, $range->startAt);
        $this->assertSame($endAt, $range->endAt);
    }

    public function test_最短30分でインスタンス化できる(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 09:00:00');
        $endAt = new DateTimeImmutable('2026-04-30 09:30:00');

        // When
        $range = new ReservationTimeRange($startAt, $endAt);

        // Then
        $this->assertSame($startAt, $range->startAt);
        $this->assertSame($endAt, $range->endAt);
    }

    public function test_最長4時間でインスタンス化できる(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 18:00:00');
        $endAt = new DateTimeImmutable('2026-04-30 22:00:00');

        // When
        $range = new ReservationTimeRange($startAt, $endAt);

        // Then
        $this->assertSame($startAt, $range->startAt);
        $this->assertSame($endAt, $range->endAt);
    }

    public function test_同じ値を持つ2つのインスタンスは等値である(): void
    {
        // Given
        $range1 = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 09:00:00'),
            new DateTimeImmutable('2026-04-30 10:00:00'),
        );
        $range2 = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 09:00:00'),
            new DateTimeImmutable('2026-04-30 10:00:00'),
        );

        // Then
        $this->assertTrue($range1->equals($range2));
    }

    public function test_異なる値を持つ2つのインスタンスは等値でない(): void
    {
        // Given
        $range1 = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 09:00:00'),
            new DateTimeImmutable('2026-04-30 10:00:00'),
        );
        $range2 = new ReservationTimeRange(
            new DateTimeImmutable('2026-04-30 10:00:00'),
            new DateTimeImmutable('2026-04-30 11:00:00'),
        );

        // Then
        $this->assertFalse($range1->equals($range2));
    }

    public function test_終了時刻が開始時刻より前の場合は例外が発生する(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 10:00:00');
        $endAt = new DateTimeImmutable('2026-04-30 09:00:00');

        // When / Then
        $this->expectException(InvalidArgumentException::class);
        new ReservationTimeRange($startAt, $endAt);
    }

    public function test_終了時刻が開始時刻と同じ場合は例外が発生する(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 10:00:00');
        $endAt = new DateTimeImmutable('2026-04-30 10:00:00');

        // When / Then
        $this->expectException(InvalidArgumentException::class);
        new ReservationTimeRange($startAt, $endAt);
    }

    public function test_開始時刻が営業開始時刻より前の場合は例外が発生する(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 07:59:00');
        $endAt = new DateTimeImmutable('2026-04-30 09:00:00');

        // When / Then
        $this->expectException(InvalidArgumentException::class);
        new ReservationTimeRange($startAt, $endAt);
    }

    public function test_終了時刻が営業終了時刻より後の場合は例外が発生する(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 21:00:00');
        $endAt = new DateTimeImmutable('2026-04-30 22:01:00');

        // When / Then
        $this->expectException(InvalidArgumentException::class);
        new ReservationTimeRange($startAt, $endAt);
    }

    public function test_29分の予約は例外が発生する(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 09:00:00');
        $endAt = new DateTimeImmutable('2026-04-30 09:29:00');

        // When / Then
        $this->expectException(InvalidArgumentException::class);
        new ReservationTimeRange($startAt, $endAt);
    }

    public function test_241分の予約は例外が発生する(): void
    {
        // Given
        $startAt = new DateTimeImmutable('2026-04-30 08:00:00');
        $endAt = new DateTimeImmutable('2026-04-30 12:01:00');

        // When / Then
        $this->expectException(InvalidArgumentException::class);
        new ReservationTimeRange($startAt, $endAt);
    }
}

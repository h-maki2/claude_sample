<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use PHPUnit\Framework\TestCase;

class ReservationIdTest extends TestCase
{
    public function test_有効なUUIDv7形式の予約IDを生成できる(): void
    {
        // Given
        $value = '01957b3c-1234-7abc-8def-000000000001';

        // When
        $id = new ReservationId($value);

        // Then
        $this->assertSame($value, $id->value);
    }

    public function test_同じ予約IDは等値である(): void
    {
        // Given
        $value = '01957b3c-1234-7abc-8def-000000000001';
        $id1 = new ReservationId($value);
        $id2 = new ReservationId($value);

        // When
        $result = $id1->equals($id2);

        // Then
        $this->assertTrue($result);
    }

    public function test_異なる予約IDは等値でない(): void
    {
        // Given
        $id1 = new ReservationId('01957b3c-1234-7abc-8def-000000000001');
        $id2 = new ReservationId('01957b3c-1234-7abc-8def-000000000002');

        // When
        $result = $id1->equals($id2);

        // Then
        $this->assertFalse($result);
    }

    public function test_空の予約IDは生成できない(): void
    {
        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new ReservationId('');
    }

    public function test_UUIDv7形式でない値では予約IDを生成できない(): void
    {
        // Given: UUIDv4形式
        $value = '550e8400-e29b-41d4-a716-446655440000';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new ReservationId($value);
    }
}

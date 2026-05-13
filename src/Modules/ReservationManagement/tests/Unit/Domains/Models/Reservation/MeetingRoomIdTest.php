<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use PHPUnit\Framework\TestCase;

class MeetingRoomIdTest extends TestCase
{
    public function test_文字列でインスタンス化できる(): void
    {
        // Given
        $value = '01957b3c-1234-7abc-8def-000000000001';

        // When
        $id = new MeetingRoomId($value);

        // Then
        $this->assertSame($value, $id->value);
    }

    public function test_同じ値を持つ2つのインスタンスは等値である(): void
    {
        // Given
        $value = '01957b3c-1234-7abc-8def-000000000001';
        $id1 = new MeetingRoomId($value);
        $id2 = new MeetingRoomId($value);

        // Then
        $this->assertTrue($id1->equals($id2));
    }

    public function test_異なる値を持つ2つのインスタンスは等値でない(): void
    {
        // Given
        $id1 = new MeetingRoomId('01957b3c-1234-7abc-8def-000000000001');
        $id2 = new MeetingRoomId('01957b3c-1234-7abc-8def-000000000002');

        // Then
        $this->assertFalse($id1->equals($id2));
    }
}

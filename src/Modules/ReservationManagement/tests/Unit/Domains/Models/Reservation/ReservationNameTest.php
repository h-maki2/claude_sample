<?php

namespace Tests\ReservationManagement\Unit\Domains\Models\Reservation;

use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use PHPUnit\Framework\TestCase;

class ReservationNameTest extends TestCase
{
    public function test_1文字の予約名でインスタンス化できる(): void
    {
        // Given
        $value = 'A';

        // When
        $name = new ReservationName($value);

        // Then
        $this->assertSame($value, $name->value);
    }

    public function test_50文字の予約名でインスタンス化できる(): void
    {
        // Given
        $value = str_repeat('あ', 50);

        // When
        $name = new ReservationName($value);

        // Then
        $this->assertSame($value, $name->value);
    }

    public function test_同じ値を持つ2つのインスタンスは等値である(): void
    {
        // Given
        $name1 = new ReservationName('第1回定例MTG');
        $name2 = new ReservationName('第1回定例MTG');

        // Then
        $this->assertTrue($name1->equals($name2));
    }

    public function test_異なる値を持つ2つのインスタンスは等値でない(): void
    {
        // Given
        $name1 = new ReservationName('第1回定例MTG');
        $name2 = new ReservationName('第2回定例MTG');

        // Then
        $this->assertFalse($name1->equals($name2));
    }

    public function test_空文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = '';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new ReservationName($value);
    }

    public function test_51文字の文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = str_repeat('あ', 51);

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new ReservationName($value);
    }
}

<?php

namespace Tests\MeetingRoomManagement\Unit\Domains\Models\MeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use PHPUnit\Framework\TestCase;

class CapacityTest extends TestCase
{
    public function test_1名の定員でインスタンス化できる(): void
    {
        // Given
        $value = 1;

        // When
        $capacity = new Capacity($value);

        // Then
        $this->assertSame(1, $capacity->value);
    }

    public function test_50名の定員でインスタンス化できる(): void
    {
        // Given
        $value = 50;

        // When
        $capacity = new Capacity($value);

        // Then
        $this->assertSame(50, $capacity->value);
    }

    public function test_プロパティvalueで整数値を直接取得できる(): void
    {
        // Given
        $value = 10;

        // When
        $capacity = new Capacity($value);

        // Then
        $this->assertSame(10, $capacity->value);
    }

    public function test_同じ値を持つ2つのインスタンスは等値である(): void
    {
        // Given
        $capacity1 = new Capacity(20);
        $capacity2 = new Capacity(20);

        // Then
        $this->assertTrue($capacity1->equals($capacity2));
    }

    public function test_異なる値を持つ2つのインスタンスは等値でない(): void
    {
        // Given
        $capacity1 = new Capacity(10);
        $capacity2 = new Capacity(30);

        // Then
        $this->assertFalse($capacity1->equals($capacity2));
    }

    public function test_0名を渡した場合は例外が発生する(): void
    {
        // Given
        $value = 0;

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('定員は1名以上で入力してください。');
        new Capacity($value);
    }

    public function test_負数を渡した場合は例外が発生する(): void
    {
        // Given
        $value = -1;

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('定員は1名以上で入力してください。');
        new Capacity($value);
    }

    public function test_51名を渡した場合は例外が発生する(): void
    {
        // Given
        $value = 51;

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('定員は50名以下で入力してください。');
        new Capacity($value);
    }
}

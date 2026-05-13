<?php

namespace Tests\MeetingRoomManagement\Unit\Domains\Models\MeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use PHPUnit\Framework\TestCase;

class MeetingRoomNameTest extends TestCase
{
    public function test_1文字の会議室名でインスタンス化できる(): void
    {
        // Given
        $value = 'A';

        // When
        $name = new MeetingRoomName($value);

        // Then
        $this->assertInstanceOf(MeetingRoomName::class, $name);
    }

    public function test_50文字の会議室名でインスタンス化できる(): void
    {
        // Given
        $value = str_repeat('あ', 50);

        // When
        $name = new MeetingRoomName($value);

        // Then
        $this->assertInstanceOf(MeetingRoomName::class, $name);
    }

    public function test_プロパティvalueで文字列値を直接取得できる(): void
    {
        // Given
        $value = '第1会議室';

        // When
        $name = new MeetingRoomName($value);

        // Then
        $this->assertSame('第1会議室', $name->value);
    }

    public function test_同じ値を持つ2つのインスタンスは等値である(): void
    {
        // Given
        $name1 = new MeetingRoomName('第1会議室');
        $name2 = new MeetingRoomName('第1会議室');

        // Then
        $this->assertTrue($name1->equals($name2));
    }

    public function test_異なる値を持つ2つのインスタンスは等値でない(): void
    {
        // Given
        $name1 = new MeetingRoomName('第1会議室');
        $name2 = new MeetingRoomName('第2会議室');

        // Then
        $this->assertFalse($name1->equals($name2));
    }

    public function test_空文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = '';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new MeetingRoomName($value);
    }

    public function test_51文字の文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = str_repeat('あ', 51);

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new MeetingRoomName($value);
    }
}

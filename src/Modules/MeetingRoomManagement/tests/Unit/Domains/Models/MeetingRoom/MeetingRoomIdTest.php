<?php

namespace Tests\MeetingRoomManagement\Unit\Domains\Models\MeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use PHPUnit\Framework\TestCase;

class MeetingRoomIdTest extends TestCase
{
    public function test_有効なUUID文字列でインスタンス化できる(): void
    {
        // Given
        $value = '01957b3c-1234-7abc-8def-000000000001';

        // When
        $id = new MeetingRoomId($value);

        // Then
        $this->assertInstanceOf(MeetingRoomId::class, $id);
    }

    public function test_プロパティvalueで文字列値を取得できる(): void
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

    public function test_空文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = '';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new MeetingRoomId($value);
    }

    public function test_UUIDv7形式でない文字列を渡した場合は例外が発生する(): void
    {
        // Given
        $value = 'not-a-valid-uuid';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new MeetingRoomId($value);
    }

    public function test_UUIDv4形式の文字列を渡した場合は例外が発生する(): void
    {
        // Given: UUIDv4（バージョンビットが4）
        $value = '550e8400-e29b-41d4-a716-446655440000';

        // When / Then
        $this->expectException(\InvalidArgumentException::class);
        new MeetingRoomId($value);
    }
}

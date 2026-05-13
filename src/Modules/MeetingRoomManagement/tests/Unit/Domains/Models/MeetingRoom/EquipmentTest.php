<?php

namespace Tests\MeetingRoomManagement\Unit\Domains\Models\MeetingRoom;

use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use PHPUnit\Framework\TestCase;

class EquipmentTest extends TestCase
{
    public function test_ホワイトボードをDB値から復元できる(): void
    {
        // Given
        $dbValue = 1;

        // When
        $equipment = Equipment::from($dbValue);

        // Then
        $this->assertSame(Equipment::WHITEBOARD, $equipment);
    }

    public function test_プロジェクターをDB値から復元できる(): void
    {
        // Given
        $dbValue = 2;

        // When
        $equipment = Equipment::from($dbValue);

        // Then
        $this->assertSame(Equipment::PROJECTOR, $equipment);
    }

    public function test_モニターをDB値から復元できる(): void
    {
        // Given
        $dbValue = 3;

        // When
        $equipment = Equipment::from($dbValue);

        // Then
        $this->assertSame(Equipment::MONITOR, $equipment);
    }

    public function test_ビデオ会議システムをDB値から復元できる(): void
    {
        // Given
        $dbValue = 4;

        // When
        $equipment = Equipment::from($dbValue);

        // Then
        $this->assertSame(Equipment::VIDEO_CONFERENCE_SYSTEM, $equipment);
    }

    public function test_各備品の表示用文字列を返す(): void
    {
        // Given / When / Then（match の全ケース網羅を一括確認）
        $this->assertSame('ホワイトボード', Equipment::WHITEBOARD->label());
        $this->assertSame('プロジェクター', Equipment::PROJECTOR->label());
        $this->assertSame('モニター', Equipment::MONITOR->label());
        $this->assertSame('ビデオ会議システム', Equipment::VIDEO_CONFERENCE_SYSTEM->label());
    }

}

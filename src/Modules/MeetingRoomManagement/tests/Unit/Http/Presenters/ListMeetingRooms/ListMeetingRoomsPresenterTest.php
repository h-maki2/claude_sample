<?php

namespace Tests\MeetingRoomManagement\Unit\Http\Presenters\ListMeetingRooms;

use Modules\MeetingRoomManagement\Http\Presenters\ListMeetingRooms\ListMeetingRoomsPresenter;
use Modules\MeetingRoomManagement\UseCase\ListMeetingRooms\MeetingRoomListItem;
use PHPUnit\Framework\TestCase;

class ListMeetingRoomsPresenterTest extends TestCase
{
    public function test_会議室一覧が空のとき空のリストを返す(): void
    {
        // Given
        $presenter = new ListMeetingRoomsPresenter([]);

        // When
        $result = $presenter->getMeetingRooms();

        // Then
        $this->assertSame([], $result);
    }

    public function test_会議室一覧を正しい形式に変換する(): void
    {
        // Given
        $listItems = [
            new MeetingRoomListItem(
                meetingRoomId: '01957b3c-1234-7abc-8def-000000000001',
                name: '第1会議室',
                capacity: 10,
                equipments: ['ホワイトボード', 'プロジェクター'],
            ),
            new MeetingRoomListItem(
                meetingRoomId: '01957b3c-1234-7abc-8def-000000000002',
                name: '第2会議室',
                capacity: 20,
                equipments: ['モニター'],
            ),
        ];
        $presenter = new ListMeetingRoomsPresenter($listItems);

        // When
        $result = $presenter->getMeetingRooms();

        // Then
        $this->assertCount(2, $result);

        $this->assertSame('01957b3c-1234-7abc-8def-000000000001', $result[0]['meetingRoomId']);
        $this->assertSame('第1会議室', $result[0]['name']);
        $this->assertSame(10, $result[0]['capacity']);
        $this->assertSame(['ホワイトボード', 'プロジェクター'], $result[0]['equipments']);

        $this->assertSame('01957b3c-1234-7abc-8def-000000000002', $result[1]['meetingRoomId']);
        $this->assertSame('第2会議室', $result[1]['name']);
        $this->assertSame(20, $result[1]['capacity']);
        $this->assertSame(['モニター'], $result[1]['equipments']);
    }

    public function test_備品が0件のとき空の配列を返す(): void
    {
        // Given
        $listItems = [
            new MeetingRoomListItem(
                meetingRoomId: '01957b3c-1234-7abc-8def-000000000001',
                name: '第1会議室',
                capacity: 10,
                equipments: [],
            ),
        ];
        $presenter = new ListMeetingRoomsPresenter($listItems);

        // When
        $result = $presenter->getMeetingRooms();

        // Then
        $this->assertSame([], $result[0]['equipments']);
    }
}

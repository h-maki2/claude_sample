<?php

namespace Tests\MeetingRoomManagement\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom\EloquentMeetingRoomRepository;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\MeetingRoomTestDataCreator;
use Tests\TestCase;

class ListMeetingRoomsTest extends TestCase
{
    use RefreshDatabase;

    private MeetingRoomTestDataCreator $creator;

    public function setUp(): void
    {
        parent::setUp();
        $this->creator = new MeetingRoomTestDataCreator(
            app(EloquentMeetingRoomRepository::class),
        );
    }

    public function test_会議室が登録されていないとき空のリストを返す(): void
    {
        // Given: 会議室なし

        // When
        $response = $this->getJson('/api/v1/meeting-rooms');

        // Then
        $response->assertStatus(200);
        $response->assertJson(['meetingRooms' => []]);
    }

    public function test_登録されている会議室の一覧を返す(): void
    {
        // Given
        $this->creator->create(
            id: new MeetingRoomId('01957b3c-1234-7abc-8def-000000000001'),
            name: new MeetingRoomName('第1会議室'),
            capacity: new Capacity(10),
            equipments: [Equipment::WHITEBOARD, Equipment::PROJECTOR],
        );
        $this->creator->create(
            id: new MeetingRoomId('01957b3c-1234-7abc-8def-000000000002'),
            name: new MeetingRoomName('第2会議室'),
            capacity: new Capacity(20),
            equipments: [],
        );

        // When
        $response = $this->getJson('/api/v1/meeting-rooms');

        // Then
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'meetingRooms');
        $response->assertJsonFragment([
            'meetingRoomId' => '01957b3c-1234-7abc-8def-000000000001',
            'name'          => '第1会議室',
            'capacity'      => 10,
            'equipments'    => ['ホワイトボード', 'プロジェクター'],
        ]);
        $response->assertJsonFragment([
            'meetingRoomId' => '01957b3c-1234-7abc-8def-000000000002',
            'name'          => '第2会議室',
            'capacity'      => 20,
            'equipments'    => [],
        ]);
    }
}

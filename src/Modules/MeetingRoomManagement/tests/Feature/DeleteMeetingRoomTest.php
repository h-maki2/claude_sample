<?php

namespace Tests\MeetingRoomManagement\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;
use Modules\ReservationManagement\Contracts\Reservation\ReservationExistenceChecker;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\MeetingRoomTestDataCreator;
use Tests\TestCase;

class DeleteMeetingRoomTest extends TestCase
{
    use RefreshDatabase;

    private MeetingRoomRepository $repository;
    private MeetingRoomTestDataCreator $meetingRoomTestDataCreator;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(MeetingRoomRepository::class);
        $this->meetingRoomTestDataCreator = new MeetingRoomTestDataCreator($this->repository);
    }

    public function test_会議室を削除できる(): void
    {
        // Given
        $room = $this->meetingRoomTestDataCreator->create();

        // When
        $response = $this->deleteJson('/api/v1/meeting-rooms/' . $room->meetingRoomId()->value);

        // Then
        $response->assertStatus(204);
        $deleted = $this->repository->findById($room->meetingRoomId());
        $this->assertNull($deleted);
    }

    public function test_存在しない会議室IDを指定した場合は削除できない(): void
    {
        // Given: 会議室を登録しない（存在しない UUID を使用）
        $nonExistentId = '01957b3c-1234-7abc-8def-000000000099';

        // When
        $response = $this->deleteJson('/api/v1/meeting-rooms/' . $nonExistentId);

        // Then
        $response->assertStatus(404);
        $response->assertJson(['message' => '会議室が見つかりません。']);
    }
}

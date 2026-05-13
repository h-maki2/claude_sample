<?php

namespace Tests\MeetingRoomManagement\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;
use Tests\MeetingRoomManagement\Helpers\Domains\Models\MeetingRoom\MeetingRoomTestDataCreator;
use Tests\TestCase;

class UpdateMeetingRoomTest extends TestCase
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

    public function test_備品なしで会議室を編集できる(): void
    {
        // Given
        $room = $this->meetingRoomTestDataCreator->create();
        $newName = '更新後の会議室';
        $newCapacity = 30;

        // When
        $response = $this->putJson(
            '/api/v1/meeting-rooms/' . $room->meetingRoomId()->value,
            [
                'name'       => $newName,
                'capacity'   => $newCapacity,
                'equipments' => [],
            ]
        );

        // Then
        $response->assertStatus(204);
        $updated = $this->repository->findById($room->meetingRoomId());
        $this->assertSame($newName, $updated->name()->value);
        $this->assertSame($newCapacity, $updated->capacity()->value);
        $this->assertSame([], $updated->equipments());
    }

    public function test_備品ありで会議室を編集できる(): void
    {
        // Given
        $room = $this->meetingRoomTestDataCreator->create();
        $newName = '大会議室';
        $newCapacity = 50;

        // When
        $response = $this->putJson(
            '/api/v1/meeting-rooms/' . $room->meetingRoomId()->value,
            [
                'name'       => $newName,
                'capacity'   => $newCapacity,
                'equipments' => [1, 2],
            ]
        );

        // Then
        $response->assertStatus(204);
        $updated = $this->repository->findById($room->meetingRoomId());
        $this->assertSame($newName, $updated->name()->value);
        $this->assertSame($newCapacity, $updated->capacity()->value);
        $this->assertEqualsCanonicalizing([Equipment::WHITEBOARD, Equipment::PROJECTOR], $updated->equipments());
    }

    public function test_存在しない会議室IDを指定した場合は編集に失敗する(): void
    {
        // Given: 会議室を登録しない（UUIDv7形式で存在しないID）
        $nonExistentId = '01957b3c-1234-7abc-8def-000000000099';

        // When
        $response = $this->putJson(
            '/api/v1/meeting-rooms/' . $nonExistentId,
            [
                'name'       => '第1会議室',
                'capacity'   => 10,
                'equipments' => [],
            ]
        );

        // Then
        $response->assertStatus(404);
        $response->assertJson(['message' => '会議室が見つかりません。']);
    }

    public function test_会議室名が未入力の場合は会議室を編集できない(): void
    {
        // Given
        $room = $this->meetingRoomTestDataCreator->create();

        // When
        $response = $this->putJson(
            '/api/v1/meeting-rooms/' . $room->meetingRoomId()->value,
            [
                'capacity'   => 10,
                'equipments' => [],
            ]
        );

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_定員が設定可能範囲外の場合は会議室を編集できない(): void
    {
        // Given
        $room = $this->meetingRoomTestDataCreator->create();

        // When
        $response = $this->putJson(
            '/api/v1/meeting-rooms/' . $room->meetingRoomId()->value,
            [
                'name'       => '第1会議室',
                'capacity'   => 0,
                'equipments' => [],
            ]
        );

        // Then
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['capacity']);
    }
}

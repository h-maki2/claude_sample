<?php

use Illuminate\Support\Facades\Route;
use Modules\MeetingRoomManagement\Http\Controllers\CreateMeetingRoom\CreateMeetingRoomController;
use Modules\MeetingRoomManagement\Http\Controllers\ListMeetingRooms\ListMeetingRoomsController;
use Modules\MeetingRoomManagement\Http\Controllers\DeleteMeetingRoom\DeleteMeetingRoomController;
use Modules\MeetingRoomManagement\Http\Controllers\UpdateMeetingRoom\UpdateMeetingRoomController;

Route::prefix('v1')->group(function () {
    Route::get('meeting-rooms', ListMeetingRoomsController::class);
    Route::post('meeting-rooms', CreateMeetingRoomController::class);
    Route::put('meeting-rooms/{meetingRoomId}', UpdateMeetingRoomController::class);
    Route::delete('meeting-rooms/{meetingRoomId}', DeleteMeetingRoomController::class);
});

<?php

namespace Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom;

use Illuminate\Database\Eloquent\Model;

class MeetingRoomEquipmentModel extends Model
{
    protected $table = 'meeting_room_equipments';
    public $timestamps = false;

    protected $fillable = [
        'meeting_room_id',
        'equipment',
    ];
}

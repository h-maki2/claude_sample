<?php

namespace Modules\MeetingRoomManagement\Infrastructure\Repository\Eloquent\MeetingRoom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingRoomModel extends Model
{
    protected $table = 'meeting_rooms';
    protected $primaryKey = 'meeting_room_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'meeting_room_id',
        'name',
        'capacity',
    ];

    public function equipments(): HasMany
    {
        return $this->hasMany(
            MeetingRoomEquipmentModel::class,
            'meeting_room_id',
            'meeting_room_id',
        );
    }
}

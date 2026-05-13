<?php

namespace Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation;

use Illuminate\Database\Eloquent\Model;

class ReservationModel extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'reservation_id',
        'meeting_room_id',
        'name',
        'contact_person_name',
        'contact_email',
        'started_at',
        'ended_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}

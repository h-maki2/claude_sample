<?php

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;

class MeetingRoomHasActiveReservationsException extends \DomainException
{
    public function __construct(string $meetingRoomId)
    {
        parent::__construct("会議室 {$meetingRoomId} には有効な予約が存在するため削除できません。");
    }
}

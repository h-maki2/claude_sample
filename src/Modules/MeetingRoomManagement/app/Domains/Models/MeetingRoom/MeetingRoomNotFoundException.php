<?php

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;

class MeetingRoomNotFoundException extends \DomainException
{
    public function __construct(string $meetingRoomId = '')
    {
        $message = $meetingRoomId !== ''
            ? "会議室が見つかりません。ID: {$meetingRoomId}"
            : '会議室が見つかりません。';
        parent::__construct($message);
    }
}

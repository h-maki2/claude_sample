<?php

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;

enum Equipment: int
{
    case WHITEBOARD = 1;
    case PROJECTOR = 2;
    case MONITOR = 3;
    case VIDEO_CONFERENCE_SYSTEM = 4;

    public function label(): string
    {
        return match($this) {
            Equipment::WHITEBOARD => 'ホワイトボード',
            Equipment::PROJECTOR => 'プロジェクター',
            Equipment::MONITOR => 'モニター',
            Equipment::VIDEO_CONFERENCE_SYSTEM => 'ビデオ会議システム',
        };
    }
}

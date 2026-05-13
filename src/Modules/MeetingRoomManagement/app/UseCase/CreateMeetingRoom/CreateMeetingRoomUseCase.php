<?php

namespace Modules\MeetingRoomManagement\UseCase\CreateMeetingRoom;

use App\UseCase\Share\TransactionExecutor;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoom;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;

class CreateMeetingRoomUseCase
{
    public function __construct(
        private MeetingRoomRepository $meetingRoomRepository,
        private TransactionExecutor $transactionExecutor,
    ) {}

    public function execute(CreateMeetingRoomInput $input): void
    {
        $this->transactionExecutor->perform(function () use ($input) {
            $meetingRoomId = $this->meetingRoomRepository->nextId();
            $meetingRoom = new MeetingRoom(
                $meetingRoomId,
                new MeetingRoomName($input->name),
                new Capacity($input->capacity),
                array_map(fn(int $v) => Equipment::from($v), $input->equipments),
            );
            $this->meetingRoomRepository->save($meetingRoom);
        });
    }
}

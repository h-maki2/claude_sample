<?php

namespace Modules\MeetingRoomManagement\UseCase\UpdateMeetingRoom;

use App\UseCase\Share\TransactionExecutor;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Capacity;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\Equipment;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomName;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomNotFoundException;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;

class UpdateMeetingRoomUseCase
{
    public function __construct(
        private MeetingRoomRepository $meetingRoomRepository,
        private TransactionExecutor $transactionExecutor,
    ) {}

    public function execute(UpdateMeetingRoomInput $input): void
    {
        $this->transactionExecutor->perform(function () use ($input) {
            $meetingRoom = $this->meetingRoomRepository->findById(new MeetingRoomId($input->meetingRoomId));
            if ($meetingRoom === null) {
                throw new MeetingRoomNotFoundException($input->meetingRoomId);
            }
            $meetingRoom->update(
                new MeetingRoomName($input->name),
                new Capacity($input->capacity),
                array_map(fn(int $v) => Equipment::from($v), $input->equipments),
            );
            $this->meetingRoomRepository->save($meetingRoom);
        });
    }
}

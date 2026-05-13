<?php

namespace Modules\MeetingRoomManagement\UseCase\DeleteMeetingRoom;

use App\UseCase\Share\TransactionExecutor;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomHasActiveReservationsException;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomId;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomNotFoundException;
use Modules\MeetingRoomManagement\Domains\Models\MeetingRoom\MeetingRoomRepository;
use Modules\ReservationManagement\Contracts\Reservation\ReservationExistenceChecker;

class DeleteMeetingRoomUseCase
{
    public function __construct(
        private MeetingRoomRepository $meetingRoomRepository,
        private TransactionExecutor $transactionExecutor,
        private ReservationExistenceChecker $reservationExistenceChecker,
    ) {}

    public function execute(string $meetingRoomId): void
    {
        $this->transactionExecutor->perform(function () use ($meetingRoomId) {
            $meetingRoom = $this->meetingRoomRepository->findById(new MeetingRoomId($meetingRoomId));
            if ($meetingRoom === null) {
                throw new MeetingRoomNotFoundException($meetingRoomId);
            }
            if ($this->reservationExistenceChecker->hasActiveReservationsByMeetingRoomId($meetingRoomId)) {
                throw new MeetingRoomHasActiveReservationsException($meetingRoomId);
            }
            $this->meetingRoomRepository->delete($meetingRoom->meetingRoomId());
        });
    }
}

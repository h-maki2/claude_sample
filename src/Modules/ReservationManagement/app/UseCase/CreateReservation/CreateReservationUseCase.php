<?php

namespace Modules\ReservationManagement\UseCase\CreateReservation;

use App\Domains\Models\Share\Clock\Clock;
use App\UseCase\Share\TransactionExecutor;
use Modules\MeetingRoomManagement\Contracts\MeetingRoom\MeetingRoomFetcher;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactEmail;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactPersonName;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\Reservation;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;

class CreateReservationUseCase
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private MeetingRoomFetcher    $meetingRoomFetcher,
        private TransactionExecutor   $transactionExecutor,
        private Clock                 $clock,
    ) {}

    public function execute(CreateReservationInput $input): void
    {
        $meetingRoomId = new MeetingRoomId($input->meetingRoomId);

        if ($this->meetingRoomFetcher->fetchById($input->meetingRoomId) === null) {
            throw new \DomainException('指定された会議室が見つかりません。');
        }

        $reservationId = $this->reservationRepository->nextId();
        $timeRange = new ReservationTimeRange($input->startAt, $input->endAt);

        $this->transactionExecutor->perform(function () use ($reservationId, $meetingRoomId, $input, $timeRange) {
            $reservationList = $this->reservationRepository->findActiveByMeetingRoomIdAndDate(
                $meetingRoomId,
                $input->startAt,
            );

            $reservation = Reservation::create(
                reservationId:   $reservationId,
                meetingRoomId:   $meetingRoomId,
                name:            new ReservationName($input->name),
                contactPerson:   new ContactPersonName($input->contactPerson),
                email:           new ContactEmail($input->email),
                timeRange:       $timeRange,
                reservationList: $reservationList,
                clock:           $this->clock,
            );

            $this->reservationRepository->save($reservation);
        });
    }
}

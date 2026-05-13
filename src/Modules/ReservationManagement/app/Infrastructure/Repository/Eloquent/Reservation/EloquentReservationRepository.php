<?php

namespace Modules\ReservationManagement\Infrastructure\Repository\Eloquent\Reservation;

use DateTimeImmutable;
use Illuminate\Support\Str;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactEmail;
use Modules\ReservationManagement\Domains\Models\Reservation\ContactPersonName;
use Modules\ReservationManagement\Domains\Models\Reservation\MeetingRoomId;
use Modules\ReservationManagement\Domains\Models\Reservation\Reservation;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationId;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationList;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationName;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationRepository;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationStatus;
use Modules\ReservationManagement\Domains\Models\Reservation\ReservationTimeRange;

class EloquentReservationRepository implements ReservationRepository
{
    public function nextId(): ReservationId
    {
        return new ReservationId((string) Str::uuid7());
    }

    public function findById(ReservationId $id): ?Reservation
    {
        $model = ReservationModel::find($id->value);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(Reservation $reservation): void
    {
        ReservationModel::updateOrCreate(
            ['reservation_id' => $reservation->reservationId()->value],
            [
                'meeting_room_id' => $reservation->meetingRoomId()->value,
                'name' => $reservation->name()->value,
                'contact_person_name' => $reservation->contactPerson()->value,
                'contact_email' => $reservation->email()->value,
                'started_at' => $reservation->timeRange()->startAt->format('Y-m-d H:i:s'),
                'ended_at' => $reservation->timeRange()->endAt->format('Y-m-d H:i:s'),
                'status' => $reservation->status()->value,
            ],
        );
    }

    public function findActiveByDate(DateTimeImmutable $date): ReservationList
    {
        return new ReservationList(
            ReservationModel::query()
                ->whereDate('started_at', $date->format('Y-m-d'))
                ->where('status', ReservationStatus::CONFIRMED->value)
                ->get()
                ->map(fn(ReservationModel $model) => $this->toDomain($model))
                ->all()
        );
    }

    public function findActiveByMeetingRoomIdAndDate(
        MeetingRoomId $meetingRoomId,
        DateTimeImmutable $date,
    ): ReservationList {
        return new ReservationList(
            ReservationModel::query()
                ->where('meeting_room_id', $meetingRoomId->value)
                ->whereDate('started_at', $date->format('Y-m-d'))
                ->where('status', ReservationStatus::CONFIRMED->value)
                ->get()
                ->map(fn(ReservationModel $model) => $this->toDomain($model))
                ->all()
        );
    }

    private function toDomain(ReservationModel $model): Reservation
    {
        return Reservation::reconstruct(
            new ReservationId($model->reservation_id),
            new MeetingRoomId($model->meeting_room_id),
            new ReservationName($model->name),
            new ContactPersonName($model->contact_person_name),
            new ContactEmail($model->contact_email),
            new ReservationTimeRange(
                DateTimeImmutable::createFromInterface($model->started_at),
                DateTimeImmutable::createFromInterface($model->ended_at),
            ),
            ReservationStatus::from($model->status),
        );
    }
}

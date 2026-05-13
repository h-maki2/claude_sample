<?php

namespace Modules\MeetingRoomManagement\Domains\Models\MeetingRoom;

class MeetingRoom
{
    /**
     * @param Equipment[] $equipments
     */
    public function __construct(
        private readonly MeetingRoomId $meetingRoomId,
        private MeetingRoomName $name,
        private Capacity $capacity,
        private array $equipments,
    ) {
        $this->validateEquipments($equipments);
    }

    public function meetingRoomId(): MeetingRoomId
    {
        return $this->meetingRoomId;
    }

    public function name(): MeetingRoomName
    {
        return $this->name;
    }

    public function capacity(): Capacity
    {
        return $this->capacity;
    }

    /** @return Equipment[] */
    public function equipments(): array
    {
        return $this->equipments;
    }

    /** @param Equipment[] $equipments */
    public function update(MeetingRoomName $name, Capacity $capacity, array $equipments): void
    {
        $this->validateEquipments($equipments);
        $this->name = $name;
        $this->capacity = $capacity;
        $this->equipments = $equipments;
    }

    /** @param Equipment[] $equipments */
    private function validateEquipments(array $equipments): void
    {
        foreach ($equipments as $equipment) {
            // @phpstan-ignore-next-line instanceof.alwaysTrue
            if (!$equipment instanceof Equipment) {
                throw new \InvalidArgumentException('equipments には Equipment enum のみ指定できます。');
            }
        }
        if (count(array_unique($equipments, SORT_REGULAR)) !== count($equipments)) {
            throw new \InvalidArgumentException('備品は重複して指定できません。');
        }
    }
}

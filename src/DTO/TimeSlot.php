<?php

namespace App\DTO;

class TimeSlot
{
    public function __construct(
        public readonly \DateTime $start,
        public readonly \DateTime $end,
        public readonly bool $available,
        public readonly ?string $reason = null,
    ) {}

    /**
     * Convertit le créneau en tableau pour l'API
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->format('Y-m-d\TH:i:s'),
            'end' => $this->end->format('Y-m-d\TH:i:s'),
            'available' => $this->available,
            'reason' => $this->reason,
        ];
    }

    /**
     * Crée un TimeSlot depuis un tableau
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $start = is_string($data['start'])
            ? new \DateTime($data['start'])
            : $data['start'];

        $end = is_string($data['end'])
            ? new \DateTime($data['end'])
            : $data['end'];

        return new self(
            start: $start,
            end: $end,
            available: $data['available'] ?? true,
            reason: $data['reason'] ?? null
        );
    }
}

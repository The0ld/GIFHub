<?php

namespace App\DTO;

class PaginationDTO
{
    public int $totalCount;
    public int $count;
    public int $offset;

    private function __construct(int $total_count, int $count, int $offset)
    {
        $this->totalCount = $total_count;
        $this->count = $count;
        $this->offset = $offset;
    }

    /**
     * Factory method to create PaginationDTO from JSON object.
     */
    public static function fromJsonObject(?object $pagination): self
    {
        return new self(
            $pagination->total_count ?? 0,
            $pagination->count ?? 0,
            $pagination->offset ?? 0
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'total_count' => $this->totalCount,
            'count' => $this->count,
            'offset' => $this->offset,
        ];
    }
}

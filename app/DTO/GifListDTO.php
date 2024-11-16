<?php

namespace App\DTO;

class GifListDTO
{
    /** @var GifClientDTO[] */
    public array $gifs;
    public PaginationDTO $pagination;

    private function __construct(array $gifs, ?PaginationDTO $pagination)
    {
        $this->gifs = $gifs;
        $this->pagination = $pagination;
    }

    /**
     * Factory method to create a GifListDTO.
     */
    public static function fromJsonObject(object $jsonObject): self
    {
        $pagination = PaginationDTO::fromJsonObject($jsonObject->pagination);

        $gifs = array_map(
            fn($gifData) => GifClientDTO::fromJsonObject($gifData),
            $jsonObject->data ?? []
        );

        return new self($gifs, $pagination);
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'gifs' => array_map(fn(GifClientDTO $gif) => $gif->toArray(), $this->gifs),
            'pagination' => $this->pagination?->toArray(),
        ];
    }
}

<?php

namespace App\DTO;

class GifClientDTO
{
    public string $id;
    public string $url;
    public string $title;
    public ImageDTO $original;
    public ImageDTO $fixed_width;
    public ImageDTO $fixed_height;

    private function __construct(
        string $id,
        string $url,
        string $title,
        ImageDTO $original,
        ImageDTO $fixed_width,
        ImageDTO $fixed_height,
    )
    {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
        $this->original = $original;
        $this->fixed_width = $fixed_width;
        $this->fixed_height = $fixed_height;
    }

    /**
     * Factory method to create DTO from a JSON object.
     */
    public static function fromJsonObject(object $jsonObject): self
    {
        return new self(
            $jsonObject->id,
            $jsonObject->url,
            $jsonObject->title,
            ImageDTO::fromJsonObject($jsonObject->images->original_still),
            ImageDTO::fromJsonObject($jsonObject->images->fixed_width),
            ImageDTO::fromJsonObject($jsonObject->images->fixed_height),
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'title' => $this->title,
            'images' => [
                'original' => $this->original->toArray(),
                'fixed_width' => $this->fixed_width->toArray(),
                'fixed_height' => $this->fixed_height->toArray(),
            ],
        ];
    }
}

<?php

namespace App\DTO;

class ImageDTO
{
    public string $url;
    public string $width;
    public string $height;

    private function __construct(string $url, string $width, string $height)
    {
        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Factory method to create ImageDTO from JSON object.
     */
    public static function fromJsonObject(object $jsonObject): self
    {
        return new self(
            $jsonObject->url,
            $jsonObject->width,
            $jsonObject->height
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}


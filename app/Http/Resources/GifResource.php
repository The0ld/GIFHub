<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GifResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
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

<?php

namespace App\DTO;

use Illuminate\Http\Request;

class FavoriteGifDTO
{
    public string $gifId;
    public string $alias;
    public int $userId;

    private function __construct(string $gifId, string $alias, int $userId)
    {
        $this->gifId = $gifId;
        $this->alias = $alias;
        $this->userId = $userId;
    }

    // Factory method to create DTO from a Request
    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->input('gif_id'),
            $request->input('alias'),
            $request->input('user_id'),
        );
    }

    public function toArray(): array
    {
        return [
            'gif_id' => $this->gifId,
            'alias' => $this->alias,
            'user_id' => $this->userId,
        ];
    }
}

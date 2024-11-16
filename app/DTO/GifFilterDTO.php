<?php

namespace App\DTO;

use Illuminate\Http\Request;

class GifFilterDTO
{
    public string $q;
    public int $limit;
    public int $offset;

    private function __construct(string $q, int $limit, int $offset)
    {
        $this->q = $q;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    // Factory method to create DTO from a Request
    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->input('q'),
            $request->input('limit'),
            $request->input('offset'),
        );
    }

    public function toArray(): array
    {
        return [
            'q' => $this->q,
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }
}

<?php

namespace App\Clients;

use App\Contracts\Clients\GifClientInterface;
use App\DTO\{GifClientDTO, GifFilterDTO, GifListDTO};
use App\Exceptions\GiphyClientException;
use Illuminate\Support\Facades\Http;

class GiphyClient implements GifClientInterface
{
    private $url;
    private $key;

    public function __construct()
    {
        $this->url = config('giphy.api_url');
        $this->key = config('giphy.api_key');
    }

    public function filterGifs(GifFilterDTO $filter): GifListDTO
    {
        $response = Http::get("{$this->url}/search", [
            'q' => $filter->q,
            'limit' => $filter->limit,
            'offset' => $filter->offset,
            'api_key' => $this->key,
        ]);

        $this->handleErrorResponse($response);

        return GifListDTO::fromJsonObject(json_decode($response->body()));
    }

    public function getGifById(string $gifId): GifClientDTO
    {
        $response = Http::get("{$this->url}/{$gifId}", [
            'api_key' => $this->key,
        ]);

        $this->handleErrorResponse($response);

        $responseData = json_decode($response->body());

        return GifClientDTO::fromJsonObject($responseData->data);
    }

    private function handleErrorResponse($response): void
    {
        if (!$response->successful()) {
            $message = $response->json('meta.msg') ?? 'Unknown error';
            $statusCode = $response->status();

            throw new GiphyClientException($message, $statusCode);
        }
    }
}

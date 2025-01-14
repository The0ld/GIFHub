<?php

namespace App\Clients;

use App\Contracts\Clients\GifClientInterface;
use App\DTO\{GifClientDTO, GifFilterDTO, GifListDTO};
use App\Exceptions\GiphyClientException;
use Illuminate\Support\Facades\Cache;
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
        // Cache key based on filter query and pagination
        $cacheKey = "gifs_search:{$filter->q}:limit_{$filter->limit}:offset_{$filter->offset}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($filter) {
            $response = Http::get("{$this->url}/search", [
                'q' => $filter->q,
                'limit' => $filter->limit,
                'offset' => $filter->offset,
                'api_key' => $this->key,
            ]);

            $this->handleErrorResponse($response);

            return GifListDTO::fromJsonObject(json_decode($response->body()));
        });
    }

    public function getGifById(string $gifId): GifClientDTO
    {
        // Cache key based on the GIF ID
        $cacheKey = "gif_show:{$gifId}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($gifId) {
            $response = Http::get("{$this->url}/{$gifId}", [
                'api_key' => $this->key,
            ]);

            $this->handleErrorResponse($response);

            $responseData = json_decode($response->body());

            return GifClientDTO::fromJsonObject($responseData->data);
        });
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

<?php

namespace App\Services;

use App\Contracts\Clients\GifClientInterface;
use App\Contracts\Services\GifServiceInterface;
use App\Contracts\Repositories\GifRepositoryInterface;
use App\DTO\{GifClientDTO, GifListDTO, GifFilterDTO, FavoriteGifDTO};

class GifService implements GifServiceInterface
{
    protected GifRepositoryInterface $gifRepository;
    protected GifClientInterface $gifClient;

    public function __construct(GifRepositoryInterface $gifRepository, GifClientInterface $gifClient)
    {
        $this->gifRepository = $gifRepository;
        $this->gifClient = $gifClient;
    }

    public function filterGifs(GifFilterDTO $filter): GifListDTO
    {
        return $this->gifClient->filterGifs($filter);
    }

    public function getGifById(string $gifId): GifClientDTO
    {
        return $this->gifClient->getGifById($gifId);
    }

    public function saveFavoriteGif(FavoriteGifDTO $favoriteGif): void
    {
        $this->gifRepository->saveFavoriteGif($favoriteGif);
    }
}

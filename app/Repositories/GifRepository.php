<?php

namespace App\Repositories;

use App\Contracts\Repositories\GifRepositoryInterface;
use App\DTO\FavoriteGifDTO;
use App\Models\FavoriteGif;

class GifRepository implements GifRepositoryInterface
{
    public function saveFavoriteGif(FavoriteGifDTO $favoriteGif): void
    {
        FavoriteGif::create($favoriteGif->toArray());
    }
}

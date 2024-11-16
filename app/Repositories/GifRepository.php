<?php

namespace App\Repositories;

use App\Contracts\Repositories\GifRepositoryInterface;
use App\DTO\FavoriteGifDTO;
use App\Exceptions\DuplicateFavoriteGifException;
use App\Models\FavoriteGif;
use Illuminate\Database\QueryException;

class GifRepository implements GifRepositoryInterface
{
    public function saveFavoriteGif(FavoriteGifDTO $favoriteGif): void
    {
        try {
            FavoriteGif::create($favoriteGif->toArray());
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new DuplicateFavoriteGifException();
            } else {
                throw $e;
            }
        }
    }
}

<?php

namespace App\Contracts\Repositories;

use App\DTO\FavoriteGifDTO;

interface GifRepositoryInterface
{
    /**
     * Saves a user's favorite GIF information to the database.
     *
     * @param FavoriteGifDTO $favoriteGif Data transfer object containing favorite GIF details,
     *                                    such as user ID, GIF ID, and alias.
     * @return void This method does not return any value.
     */
    public function saveFavoriteGif(FavoriteGifDTO $favoriteGif): void;
}

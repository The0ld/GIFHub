<?php

namespace App\Policies;

use App\DTO\FavoriteGifDTO;
use App\Models\User;

class FavoriteGifPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function save(User $authUser, FavoriteGifDTO $favoriteGif): bool
    {
        return $authUser->id === $favoriteGif->userId;
    }
}

<?php

namespace App\Contracts\Services;

use App\DTO\{FavoriteGifDTO, GifClientDTO, GifFilterDTO, GifListDTO};

interface GifServiceInterface
{

    /**
     * Retrieves a filtered list of GIFs based on the provided criteria.
     *
     * @param GifFilterDTO $filter Filter criteria, including query, limit, and offset.
     * @return GifListDTO An array of GIF client data transfer objects.
     */
    public function filterGifs(GifFilterDTO $filter): GifListDTO;

    /**
     * Retrieves detailed information for a specific GIF by its ID.
     *
     * @param string $gifId The unique identifier of the GIF.
     * @return GifClientDTO A GIF client data transfer object with detailed information.
     */
    public function getGifById(string $gifId): GifClientDTO;

    /**
     * Saves a user's favorite GIF information to the database.
     *
     * @param FavoriteGifDTO $favoriteGif Data transfer object containing favorite GIF details,
     *                                    such as user ID, GIF ID, and alias.
     * @return void This method does not return any value.
     */
    public function saveFavoriteGif(FavoriteGifDTO $favoriteGif): void;
}

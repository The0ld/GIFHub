<?php

namespace App\Contracts\Clients;

use App\DTO\{GifClientDTO, GifFilterDTO, GifListDTO};

interface GifClientInterface
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
}

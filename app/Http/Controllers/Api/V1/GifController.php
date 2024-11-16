<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\{FavoriteGifDTO, GifFilterDTO};
use App\Contracts\Services\GifServiceInterface;
use App\Exceptions\DuplicateFavoriteGifException;
use App\Exceptions\GiphyClientException;
use App\Http\Controllers\Controller;
use App\Http\Requests\{GifFilterRequest, SaveFavoriteGifRequest};
use App\Http\Resources\{GifResource, PaginationResource};
use Exception;

class GifController extends Controller
{
    protected GifServiceInterface $gifService;

    public function __construct(GifServiceInterface $gifService)
    {
        $this->gifService = $gifService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(GifFilterRequest $request)
    {
        try {
            $gifFilter = GifFilterDTO::fromRequest($request);

            $gifList = $this->gifService->filterGifs($gifFilter);
            return response()->json([
                'data' => GifResource::collection($gifList->gifs),
                'pagination' => new PaginationResource($gifList->pagination),
            ], 200);
        } catch (GiphyClientException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveFavoriteGifRequest $request)
    {
        try {
            $favoriteGif = FavoriteGifDTO::fromRequest($request);
            $this->authorize('save', $favoriteGif);

            $this->gifService->saveFavoriteGif($favoriteGif);
            return response()->json(['message' => 'GIF saved successfully.'], 201);
        } catch(DuplicateFavoriteGifException $de) {
            return response()->json(['message' => $de->getMessage()], 409);
        } catch(Exception $e) {
            return response()->json(['message' => 'Unexpected error.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $gif = $this->gifService->getGifById($id);
            return new GifResource($gif);
        } catch (GiphyClientException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
}

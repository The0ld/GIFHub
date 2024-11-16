<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\{FavoriteGifDTO, GifFilterDTO};
use App\Contracts\Services\GifServiceInterface;
use App\Exceptions\DuplicateFavoriteGifException;
use App\Exceptions\GiphyClientException;
use App\Http\Controllers\Controller;
use App\Http\Requests\{GifFilterRequest, SaveFavoriteGifRequest};
use App\Http\Resources\{GifResource, PaginationResource};
use App\Helpers\ApiResponse;
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

            return ApiResponse::success(
                data: GifResource::collection($gifList->gifs),
                statusCode: 200,
                pagination: new PaginationResource($gifList->pagination)
            );
        } catch (GiphyClientException $e) {
            return ApiResponse::error($e->getMessage(), $e->getStatusCode());
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

            return ApiResponse::success(
                message: 'GIF saved successfully.',
                statusCode: 201,
            );
        } catch(DuplicateFavoriteGifException $de) {
            return ApiResponse::error($de->getMessage(), 409);
        } catch(Exception $e) {
            return ApiResponse::error('Unexpected error.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $gif = $this->gifService->getGifById($id);

            return ApiResponse::success(
                data: new GifResource($gif),
                statusCode: 200,
            );
        } catch (GiphyClientException $e) {
            return ApiResponse::error($e->getMessage(), $e->getStatusCode());
        }
    }
}

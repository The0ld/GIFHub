<?php

namespace App\Http\Controllers\Api\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\DTO\CredentialsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\LoginResource;

class AuthController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the login request.
     */
    public function login(LoginRequest $request)
    {
        $credentials = CredentialsDTO::fromRequest($request);

        $tokenResult = $this->authService->login($credentials);
        return new LoginResource($tokenResult);
    }
}

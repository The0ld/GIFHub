<?php

namespace App\Contracts\Services;

use App\DTO\CredentialsDTO;
use Laravel\Passport\PersonalAccessTokenResult;
use Illuminate\Auth\AuthenticationException;

interface AuthServiceInterface
{

    /**
     * Handles authentication and token creation logic.
     *
     * @param CredentialsDTO $credentials Authentication credentials.
     * @return PersonalAccessTokenResult The access token generated for the authenticated user.
     * @throws AuthenticationException If the credentials are incorrect.
     */
    public function login(CredentialsDTO $credentials): PersonalAccessTokenResult;
}

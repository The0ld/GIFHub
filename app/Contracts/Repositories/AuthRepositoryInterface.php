<?php

namespace App\Contracts\Repositories;

use App\DTO\CredentialsDTO;
use App\Models\User;

interface AuthRepositoryInterface
{
    /**
     * Authenticates the user using the credentials.
     *
     * @param CredentialsDTO $credentials Authentication credentials.
     * @return User|null
     */
    public function authenticate(CredentialsDTO $credentials): ?User;

    /**
     * Gets the authenticated user.
     *
     * @return User|null
     */
    public function getAuthenticatedUser(): ?User;
}

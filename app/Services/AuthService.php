<?php

namespace App\Services;

use App\Contracts\Repositories\AuthRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\DTO\CredentialsDTO;
use Illuminate\Auth\AuthenticationException;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthService implements AuthServiceInterface
{
    protected AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $userRepository)
    {
        $this->authRepository = $userRepository;
    }

    public function login(CredentialsDTO $credentials): PersonalAccessTokenResult
    {
        if (!$user = $this->authRepository->authenticate($credentials)) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $user->createToken('Personal Access Token');
    }
}

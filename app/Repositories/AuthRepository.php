<?php

namespace App\Repositories;

use App\Contracts\Repositories\AuthRepositoryInterface;
use App\DTO\CredentialsDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    public function authenticate(CredentialsDTO $credentials): ?User
    {
        $user = User::where('email', $credentials->email)->first();
        if ($user && Hash::check($credentials->password, $user->password)) {
            return $user;
        }

        return null;
    }

    public function getAuthenticatedUser(): ?User
    {
        return auth()->user();
    }
}

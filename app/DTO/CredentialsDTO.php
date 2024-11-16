<?php

namespace App\DTO;

use Illuminate\Http\Request;

class CredentialsDTO
{
    public string $email;
    public string $password;

    private function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    // Factory method to create DTO from a Request
    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->input('email'),
            $request->input('password')
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}

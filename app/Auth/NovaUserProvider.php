<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class NovaUserProvider implements UserProvider
{

    protected HasherContract $hasher;
    protected $model;

    public function __construct(HasherContract $hasher, $model)
    {
        $this->model = $model;
        $this->hasher = $hasher;
    }

    public function retrieveById($identifier)
    {
        // TODO: Implement retrieveById() method.
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        // TODO: Implement retrieveByToken() method.
    }

    public function updateRememberToken(\Illuminate\Contracts\Auth\Authenticatable $user, #[\SensitiveParameter] $token)
    {
        // TODO: Implement updateRememberToken() method.
    }

    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        // TODO: Implement retrieveByCredentials() method.
    }

    public function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, #[\SensitiveParameter] array $credentials)
    {
        // TODO: Implement validateCredentials() method.
    }

    public function rehashPasswordIfRequired(\Illuminate\Contracts\Auth\Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false)
    {
        // TODO: Implement rehashPasswordIfRequired() method.
    }
}

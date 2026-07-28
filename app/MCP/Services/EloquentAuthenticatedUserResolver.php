<?php

namespace App\MCP\Services;

use App\MCP\Contracts\AuthenticatedUserResolverInterface;
use App\MCP\DTO\AuthenticatedUserDTO;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class EloquentAuthenticatedUserResolver implements AuthenticatedUserResolverInterface
{
    public function resolve(AuthenticatedUserDTO $user): ?Authenticatable
    {
        return User::query()->find($user->id);
    }
}

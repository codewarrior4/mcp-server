<?php

namespace App\MCP\Contracts;

use App\MCP\DTO\AuthenticatedUserDTO;
use Illuminate\Contracts\Auth\Authenticatable;

interface AuthenticatedUserResolverInterface
{
    public function resolve(AuthenticatedUserDTO $user): ?Authenticatable;
}

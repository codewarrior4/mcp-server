<?php

namespace App\MCP\DTO;

final readonly class AuthenticatedUserDTO
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function __construct(
        public int|string $id,
        public string $name,
        public string $guard,
        public array $abilities = [],
    ) {}
}

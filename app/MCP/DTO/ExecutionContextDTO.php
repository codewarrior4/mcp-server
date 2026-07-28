<?php

namespace App\MCP\DTO;

final readonly class ExecutionContextDTO
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public AuthenticatedUserDTO $user,
        public ?string $ipAddress = null,
        public ?string $requestId = null,
        public array $metadata = [],
    ) {}
}

<?php

namespace App\MCP\DTO;

final readonly class ExecutionResultDTO
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public bool $successful,
        public array $payload = [],
        public ?string $message = null,
    ) {}
}

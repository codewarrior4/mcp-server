<?php

namespace App\MCP\DTO;

final readonly class ToolRequestDTO
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        public string $toolName,
        public array $parameters,
        public ExecutionContextDTO $context,
    ) {}
}

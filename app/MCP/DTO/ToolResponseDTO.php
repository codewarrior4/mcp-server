<?php

namespace App\MCP\DTO;

final readonly class ToolResponseDTO
{
    public function __construct(
        public string $toolName,
        public bool $successful,
        public ExecutionResultDTO $result,
        public int $durationInMilliseconds = 0,
    ) {}
}

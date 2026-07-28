<?php

namespace App\MCP\DTO;

final readonly class ToolMetadataDTO
{
    /**
     * @param  array<int, string>  $scopes
     */
    public function __construct(
        public string $name,
        public string $description,
        public bool $enabled = false,
        public array $scopes = [],
    ) {}
}

<?php

namespace App\MCP\Contracts;

use App\MCP\DTO\ToolMetadataDTO;

interface ToolRegistryInterface
{
    public function register(ToolInterface $tool): void;

    public function resolve(string $toolName): ToolInterface;

    public function disable(string $toolName): void;

    public function enable(string $toolName): void;

    /**
     * @return array<int, ToolMetadataDTO>
     */
    public function all(): array;

    public function has(string $toolName): bool;
}

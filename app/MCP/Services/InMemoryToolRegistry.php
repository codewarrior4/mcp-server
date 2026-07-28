<?php

namespace App\MCP\Services;

use App\MCP\Contracts\ToolInterface;
use App\MCP\Contracts\ToolRegistryInterface;
use App\MCP\DTO\ToolMetadataDTO;
use App\MCP\Exceptions\ToolAlreadyRegisteredException;
use App\MCP\Exceptions\ToolNotFoundException;

class InMemoryToolRegistry implements ToolRegistryInterface
{
    /**
     * @var array<string, ToolInterface>
     */
    private array $tools = [];

    /**
     * @var array<string, bool>
     */
    private array $enabled = [];

    public function register(ToolInterface $tool): void
    {
        $toolName = $tool->metadata()->name;

        if ($this->has($toolName)) {
            throw ToolAlreadyRegisteredException::forName($toolName);
        }

        $this->tools[$toolName] = $tool;
        $this->enabled[$toolName] = $tool->metadata()->enabled;
    }

    public function resolve(string $toolName): ToolInterface
    {
        return $this->tools[$toolName] ?? throw ToolNotFoundException::forName($toolName);
    }

    public function disable(string $toolName): void
    {
        $this->ensureRegistered($toolName);

        $this->enabled[$toolName] = false;
    }

    public function enable(string $toolName): void
    {
        $this->ensureRegistered($toolName);

        $this->enabled[$toolName] = true;
    }

    public function all(): array
    {
        return array_map(
            fn (ToolInterface $tool): ToolMetadataDTO => $this->metadataFor($tool),
            array_values($this->tools),
        );
    }

    public function has(string $toolName): bool
    {
        return array_key_exists($toolName, $this->tools);
    }

    private function metadataFor(ToolInterface $tool): ToolMetadataDTO
    {
        $metadata = $tool->metadata();

        return new ToolMetadataDTO(
            name: $metadata->name,
            description: $metadata->description,
            enabled: $this->enabled[$metadata->name] ?? $metadata->enabled,
            scopes: $metadata->scopes,
        );
    }

    private function ensureRegistered(string $toolName): void
    {
        if (! $this->has($toolName)) {
            throw ToolNotFoundException::forName($toolName);
        }
    }
}

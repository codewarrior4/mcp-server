<?php

namespace App\MCP\Contracts;

interface FeatureFlagManagerInterface
{
    public function enabled(string $feature): bool;

    /**
     * @return array<string, bool>
     */
    public function all(): array;
}

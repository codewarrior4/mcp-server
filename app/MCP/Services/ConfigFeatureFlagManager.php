<?php

namespace App\MCP\Services;

use App\MCP\Contracts\FeatureFlagManagerInterface;

class ConfigFeatureFlagManager implements FeatureFlagManagerInterface
{
    public function enabled(string $feature): bool
    {
        return (bool) config("mcp.feature_flags.{$feature}", false);
    }

    public function all(): array
    {
        return config('mcp.feature_flags', []);
    }
}

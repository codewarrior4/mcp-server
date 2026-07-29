<?php

namespace App\MCP\Services;

use App\MCP\Contracts\FeatureFlagManagerInterface;
use Laravel\Pennant\Feature;

class PennantFeatureFlagManager implements FeatureFlagManagerInterface
{
    public function enabled(string $feature): bool
    {
        return Feature::active($feature);
    }

    public function all(): array
    {
        return collect(config('mcp.feature_flags', []))
            ->keys()
            ->mapWithKeys(fn (string $feature): array => [$feature => $this->enabled($feature)])
            ->all();
    }
}

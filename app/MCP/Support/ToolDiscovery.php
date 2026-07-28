<?php

namespace App\MCP\Support;

final class ToolDiscovery
{
    /**
     * @param  array<int, class-string>  $toolClasses
     * @return array<int, class-string>
     */
    public static function fromConfig(array $toolClasses): array
    {
        return array_values(array_unique($toolClasses));
    }
}

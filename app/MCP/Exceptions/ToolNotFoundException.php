<?php

namespace App\MCP\Exceptions;

use RuntimeException;

class ToolNotFoundException extends RuntimeException
{
    public static function forName(string $toolName): self
    {
        return new self("The [{$toolName}] tool could not be resolved.");
    }
}

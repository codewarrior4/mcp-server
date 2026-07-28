<?php

namespace App\MCP\Exceptions;

use RuntimeException;

class ToolAlreadyRegisteredException extends RuntimeException
{
    public static function forName(string $toolName): self
    {
        return new self("The [{$toolName}] tool has already been registered.");
    }
}

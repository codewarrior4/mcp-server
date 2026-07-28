<?php

namespace App\MCP\Exceptions;

use RuntimeException;

class InvalidToolConfigurationException extends RuntimeException
{
    public static function forClass(mixed $toolClass): self
    {
        $description = is_string($toolClass) ? $toolClass : get_debug_type($toolClass);

        return new self("The configured tool [{$description}] is invalid.");
    }
}

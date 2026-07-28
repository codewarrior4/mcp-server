<?php

namespace App\MCP\ValueObjects;

use InvalidArgumentException;

final readonly class ToolName
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Tool names cannot be empty.');
        }
    }
}

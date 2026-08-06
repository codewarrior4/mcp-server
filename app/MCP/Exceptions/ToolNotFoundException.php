<?php

namespace App\MCP\Exceptions;

use Illuminate\Contracts\Support\Arrayable;
use RuntimeException;

class ToolNotFoundException extends RuntimeException implements Arrayable
{
    public function __construct(public readonly string $toolName)
    {
        parent::__construct("The [{$toolName}] tool could not be resolved.");
    }

    public static function forName(string $toolName): self
    {
        return new self($toolName);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'tool' => $this->toolName,
            'message' => $this->getMessage(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function context(): array
    {
        return $this->toArray();
    }
}

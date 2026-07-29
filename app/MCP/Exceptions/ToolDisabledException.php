<?php

namespace App\MCP\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

class ToolDisabledException extends Exception implements Arrayable
{
    public function __construct(public readonly string $toolName)
    {
        parent::__construct('This tool is currently disabled.');
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

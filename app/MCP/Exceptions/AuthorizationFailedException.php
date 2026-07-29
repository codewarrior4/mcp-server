<?php

namespace App\MCP\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Throwable;

class AuthorizationFailedException extends Exception implements Arrayable
{
    public function __construct(
        public readonly string $toolName,
        string $message = 'You are not authorized to execute this tool.',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
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

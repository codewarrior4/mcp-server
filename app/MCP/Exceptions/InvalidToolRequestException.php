<?php

namespace App\MCP\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Throwable;

class InvalidToolRequestException extends Exception implements Arrayable
{
    /**
     * @param  array<string, mixed>  $errors
     */
    public function __construct(
        public readonly array $errors,
        string $message = 'The tool request is invalid.',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->toArray();
    }
}

<?php

namespace App\MCP\Contracts;

interface PromptValidatorInterface
{
    public function ensureSafe(string $prompt): void;
}

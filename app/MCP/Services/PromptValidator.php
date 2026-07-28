<?php

namespace App\MCP\Services;

use App\MCP\Contracts\PromptValidatorInterface;

class PromptValidator implements PromptValidatorInterface
{
    public function ensureSafe(string $prompt): void
    {
        if (trim($prompt) === '') {
            return;
        }
    }
}

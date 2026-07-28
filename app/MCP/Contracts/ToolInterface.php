<?php

namespace App\MCP\Contracts;

use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ExecutionResultDTO;
use App\MCP\DTO\ToolMetadataDTO;

interface ToolInterface
{
    public function metadata(): ToolMetadataDTO;

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO;
}

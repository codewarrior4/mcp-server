<?php

namespace App\MCP\Contracts;

use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ToolMetadataDTO;
use Illuminate\Auth\Access\Response;

interface AuthorizationInterface
{
    public function authorize(ToolMetadataDTO $tool, ExecutionContextDTO $context): Response;
}

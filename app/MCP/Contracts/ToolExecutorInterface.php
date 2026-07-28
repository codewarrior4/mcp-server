<?php

namespace App\MCP\Contracts;

use App\MCP\DTO\ToolRequestDTO;
use App\MCP\DTO\ToolResponseDTO;

interface ToolExecutorInterface
{
    public function execute(ToolRequestDTO $request): ToolResponseDTO;
}

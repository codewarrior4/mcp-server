<?php

namespace App\MCP\Services;

use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\DTO\ExecutionResultDTO;
use App\MCP\DTO\ToolRequestDTO;
use App\MCP\DTO\ToolResponseDTO;

class NullToolExecutor implements ToolExecutorInterface
{
    public function execute(ToolRequestDTO $request): ToolResponseDTO
    {
        return new ToolResponseDTO(
            toolName: $request->toolName,
            successful: false,
            result: new ExecutionResultDTO(
                successful: false,
                message: 'Tool execution has not been implemented yet.',
            ),
        );
    }
}

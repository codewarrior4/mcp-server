<?php

namespace App\MCP\Tools;

use App\MCP\Contracts\ToolInterface;
use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ExecutionResultDTO;
use App\MCP\DTO\ToolMetadataDTO;
use App\MCP\Exceptions\InvalidToolRequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SystemOverviewTool implements ToolInterface
{
    public function metadata(): ToolMetadataDTO
    {
        return new ToolMetadataDTO(
            name: 'system.overview',
            description: 'Return a safe operational summary of the MCP server.',
            enabled: true,
            scopes: ['system:overview'],
        );
    }

    public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
    {
        $validator = Validator::make($parameters, [
            'include_stats' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw new InvalidToolRequestException($validator->errors()->toArray());
        }

        $includeStats = (bool) ($parameters['include_stats'] ?? false);

        return new ExecutionResultDTO(
            successful: true,
            payload: [
                'server' => [
                    'application' => (string) config('app.name'),
                    'environment' => app()->environment(),
                    'default_provider' => (string) config('mcp.default_provider'),
                    'queue_connection' => (string) config('queue.default'),
                    'cache_store' => (string) config('cache.default'),
                    'database_connection' => (string) config('database.default'),
                ],
                'mcp' => [
                    'feature_enabled' => (bool) config('mcp.feature_flags.mcp-server'),
                    'audit_enabled' => (bool) config('mcp.audit_enabled'),
                    'max_parallel_tools' => (int) config('mcp.max_parallel_tools'),
                ],
                'request' => [
                    'requested_by' => $context->user->id,
                    'ability_count' => count($context->user->abilities),
                    'request_id' => $context->requestId,
                ],
                'stats' => $includeStats ? [
                    'user_count' => DB::table('users')->count(),
                ] : [],
            ],
        );
    }
}

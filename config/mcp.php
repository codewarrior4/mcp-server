<?php

use App\MCP\Enums\FeatureFlag;

return [
    'default_provider' => env('MCP_DEFAULT_PROVIDER', 'openai'),
    'tool_timeout' => (int) env('MCP_TOOL_TIMEOUT', 15),
    'tool_cache' => env('MCP_TOOL_CACHE', false),
    'audit_enabled' => (bool) env('MCP_AUDIT_ENABLED', true),
    'audit' => [
        'channel' => env('MCP_AUDIT_CHANNEL', env('LOG_CHANNEL', 'stack')),
    ],
    'feature_flags' => [
        FeatureFlag::MCPServer->value => false,
        FeatureFlag::ToolRegistry->value => false,
        FeatureFlag::AuditLog->value => false,
        FeatureFlag::ExperimentalTools->value => false,
        FeatureFlag::PremiumTools->value => false,
    ],
    'max_parallel_tools' => (int) env('MCP_MAX_PARALLEL_TOOLS', 3),
    'allowed_origins' => array_values(array_filter(explode(',', (string) env('MCP_ALLOWED_ORIGINS', '')))),
    'log_level' => env('MCP_LOG_LEVEL', 'info'),
    'max_execution_time' => (int) env('MCP_MAX_EXECUTION_TIME', 30),
    'tool_discovery' => [
        'enabled' => (bool) env('MCP_TOOL_DISCOVERY_ENABLED', true),
        'tools' => [],
    ],
];

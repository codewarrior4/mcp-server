<?php

namespace App\Recorders;

use App\Events\MCPToolExecuted;
use Laravel\Pulse\Facades\Pulse;

class MCPToolExecutions
{
    /**
     * @var array<int, class-string>
     */
    public array $listen = [
        MCPToolExecuted::class,
    ];

    public function record(MCPToolExecuted $event): void
    {
        Pulse::record(
            type: 'mcp_tool_execution',
            key: $event->response->toolName,
            value: $event->response->durationInMilliseconds,
        )->count()->max();
    }
}

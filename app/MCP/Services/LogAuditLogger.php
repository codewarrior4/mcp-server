<?php

namespace App\MCP\Services;

use App\MCP\Contracts\AuditLoggerInterface;
use App\MCP\DTO\AuditEventDTO;
use Illuminate\Support\Facades\Log;

class LogAuditLogger implements AuditLoggerInterface
{
    public function record(AuditEventDTO $event): void
    {
        Log::channel(config('mcp.audit.channel'))
            ->info('MCP tool execution recorded.', $event->toLogContext());
    }
}

<?php

namespace App\MCP\Actions;

use App\MCP\Contracts\AuditLoggerInterface;
use App\MCP\DTO\AuditEventDTO;

class RecordAuditEvent
{
    public function __construct(private AuditLoggerInterface $auditLogger) {}

    public function handle(AuditEventDTO $event): void
    {
        if (! config('mcp.audit_enabled')) {
            return;
        }

        $this->auditLogger->record($event);
    }
}

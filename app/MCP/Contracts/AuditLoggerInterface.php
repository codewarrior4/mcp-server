<?php

namespace App\MCP\Contracts;

use App\MCP\DTO\AuditEventDTO;

interface AuditLoggerInterface
{
    public function record(AuditEventDTO $event): void;
}

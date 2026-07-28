<?php

namespace App\MCP\Enums;

enum FeatureFlag: string
{
    case MCPServer = 'mcp-server';
    case ToolRegistry = 'tool-registry';
    case AuditLog = 'audit-log';
    case ExperimentalTools = 'experimental-tools';
    case PremiumTools = 'premium-tools';
}

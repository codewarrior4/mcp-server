<?php

namespace App\Events;

use App\MCP\DTO\ToolResponseDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MCPToolExecuted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ToolResponseDTO $response) {}
}

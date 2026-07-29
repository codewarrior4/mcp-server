<?php

namespace App\Events;

use App\MCP\DTO\ToolRequestDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class MCPToolExecutionFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ToolRequestDTO $request,
        public Throwable $exception,
        public int $durationInMilliseconds,
    ) {}
}

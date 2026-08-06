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

    /**
     * @return array<string, int|string|null>
     */
    public function context(): array
    {
        return [
            'tool_name' => $this->request->toolName,
            'user_id' => $this->request->context->user->id,
            'request_id' => $this->request->context->requestId,
            'ip_address' => $this->request->context->ipAddress,
            'duration_in_milliseconds' => $this->durationInMilliseconds,
        ];
    }
}

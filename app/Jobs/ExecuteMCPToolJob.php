<?php

namespace App\Jobs;

use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\DTO\AuthenticatedUserDTO;
use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ToolRequestDTO;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExecuteMCPToolJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $toolName,
        public array $parameters,
        public array $user,
        public ?string $ipAddress = null,
        public ?string $requestId = null,
        public array $metadata = [],
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ToolExecutorInterface $toolExecutor): void
    {
        $toolExecutor->execute(new ToolRequestDTO(
            toolName: $this->toolName,
            parameters: $this->parameters,
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $this->user['id'],
                    name: $this->user['name'],
                    guard: $this->user['guard'],
                    abilities: $this->user['abilities'] ?? [],
                ),
                ipAddress: $this->ipAddress,
                requestId: $this->requestId,
                metadata: $this->metadata,
            ),
        ));
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'mcp',
            'mcp:tool:'.$this->toolName,
            'mcp:user:'.$this->user['id'],
        ];
    }
}

<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ExecuteMCPToolJob;
use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\DTO\ExecutionResultDTO;
use App\MCP\DTO\ToolRequestDTO;
use App\MCP\DTO\ToolResponseDTO;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ExecuteMCPToolJobTest extends TestCase
{
    public function test_job_can_be_pushed_to_the_queue(): void
    {
        Queue::fake();

        ExecuteMCPToolJob::dispatch(
            toolName: 'reports.generate',
            parameters: ['account_id' => 1],
            user: [
                'id' => 1,
                'name' => 'Taylor Otwell',
                'guard' => 'web',
                'abilities' => ['reports:generate'],
            ],
        );

        Queue::assertPushed(ExecuteMCPToolJob::class);
    }

    public function test_job_builds_a_tool_request_for_the_executor(): void
    {
        $state = new class
        {
            public ?ToolRequestDTO $capturedRequest = null;
        };

        $executor = new class($state) implements ToolExecutorInterface
        {
            public function __construct(private object $state) {}

            public function execute(ToolRequestDTO $request): ToolResponseDTO
            {
                $this->state->capturedRequest = $request;

                return new ToolResponseDTO(
                    toolName: $request->toolName,
                    successful: true,
                    result: new ExecutionResultDTO(successful: true),
                );
            }
        };

        (new ExecuteMCPToolJob(
            toolName: 'reports.generate',
            parameters: ['account_id' => 1],
            user: [
                'id' => 1,
                'name' => 'Taylor Otwell',
                'guard' => 'web',
                'abilities' => ['reports:generate'],
            ],
            ipAddress: '127.0.0.1',
            requestId: 'req-123',
            metadata: ['source' => 'queue'],
        ))->handle($executor);

        $this->assertInstanceOf(ToolRequestDTO::class, $state->capturedRequest);
        $this->assertSame('reports.generate', $state->capturedRequest->toolName);
        $this->assertSame('req-123', $state->capturedRequest->context->requestId);
        $this->assertSame('queue', $state->capturedRequest->context->metadata['source']);
    }

    public function test_job_exposes_horizon_tags_for_tool_and_user_context(): void
    {
        $job = new ExecuteMCPToolJob(
            toolName: 'reports.generate',
            parameters: ['account_id' => 1],
            user: [
                'id' => 1,
                'name' => 'Taylor Otwell',
                'guard' => 'web',
                'abilities' => ['reports:generate'],
            ],
        );

        $this->assertSame([
            'mcp',
            'mcp:tool:reports.generate',
            'mcp:user:1',
        ], $job->tags());
    }
}

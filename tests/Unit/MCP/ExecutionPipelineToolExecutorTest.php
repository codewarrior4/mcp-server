<?php

namespace Tests\Unit\MCP;

use App\Events\MCPToolExecuted;
use App\Events\MCPToolExecutionFailed;
use App\MCP\Contracts\AuditLoggerInterface;
use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\Contracts\ToolInterface;
use App\MCP\Contracts\ToolRegistryInterface;
use App\MCP\DTO\AuditEventDTO;
use App\MCP\DTO\AuthenticatedUserDTO;
use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ExecutionResultDTO;
use App\MCP\DTO\ToolMetadataDTO;
use App\MCP\DTO\ToolRequestDTO;
use App\MCP\Exceptions\AuthorizationFailedException;
use App\MCP\Exceptions\InvalidToolRequestException;
use App\MCP\Exceptions\ToolDisabledException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExecutionPipelineToolExecutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_executor_runs_the_full_successful_pipeline(): void
    {
        Event::fake();

        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();
        $tool = new class implements ToolInterface
        {
            public function metadata(): ToolMetadataDTO
            {
                return new ToolMetadataDTO(
                    name: 'reports.generate',
                    description: 'Generate a report.',
                    enabled: true,
                    scopes: ['reports:generate'],
                );
            }

            public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
            {
                return new ExecutionResultDTO(
                    successful: true,
                    payload: [
                        'report_id' => 42,
                        'requested_by' => $context->user->id,
                    ],
                );
            }
        };

        app(ToolRegistryInterface::class)->register($tool);

        $auditLogger = new class implements AuditLoggerInterface
        {
            /**
             * @var array<int, AuditEventDTO>
             */
            public array $events = [];

            public function record(AuditEventDTO $event): void
            {
                $this->events[] = $event;
            }
        };

        app()->instance(AuditLoggerInterface::class, $auditLogger);

        $response = app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
            toolName: 'reports.generate',
            parameters: ['account_id' => 5, 'prompt' => 'Generate the report'],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $user->id,
                    name: $user->name,
                    guard: 'web',
                    abilities: ['reports:generate'],
                ),
                ipAddress: '127.0.0.1',
            ),
        ));

        $this->assertTrue($response->successful);
        $this->assertSame(42, $response->result->payload['report_id']);
        $this->assertCount(1, $auditLogger->events);
        $this->assertSame('reports.generate', $auditLogger->events[0]->toolName);

        Event::assertDispatched(MCPToolExecuted::class);
    }

    public function test_executor_rejects_invalid_requests(): void
    {
        config()->set('mcp.feature_flags.mcp-server', true);

        $this->expectException(InvalidToolRequestException::class);

        app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
            toolName: '',
            parameters: [],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: 1,
                    name: 'Taylor Otwell',
                    guard: 'web',
                ),
            ),
        ));
    }

    public function test_executor_blocks_when_the_server_feature_flag_is_disabled(): void
    {
        config()->set('mcp.feature_flags.mcp-server', false);

        $this->expectException(ToolDisabledException::class);

        app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
            toolName: 'reports.generate',
            parameters: ['account_id' => 5],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: 1,
                    name: 'Taylor Otwell',
                    guard: 'web',
                ),
            ),
        ));
    }

    public function test_executor_blocks_disabled_tools(): void
    {
        config()->set('mcp.feature_flags.mcp-server', true);

        $tool = new class implements ToolInterface
        {
            public function metadata(): ToolMetadataDTO
            {
                return new ToolMetadataDTO(
                    name: 'billing.refunds',
                    description: 'Issue a refund.',
                    enabled: false,
                    scopes: ['billing:refunds'],
                );
            }

            public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
            {
                return new ExecutionResultDTO(successful: true);
            }
        };

        app(ToolRegistryInterface::class)->register($tool);

        $this->expectException(ToolDisabledException::class);

        app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
            toolName: 'billing.refunds',
            parameters: ['invoice_id' => 10],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: 1,
                    name: 'Taylor Otwell',
                    guard: 'web',
                    abilities: ['billing:refunds'],
                ),
            ),
        ));
    }

    public function test_executor_blocks_unauthorized_requests(): void
    {
        Event::fake();

        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();
        $tool = new class implements ToolInterface
        {
            public function metadata(): ToolMetadataDTO
            {
                return new ToolMetadataDTO(
                    name: 'billing.refunds',
                    description: 'Issue a refund.',
                    enabled: true,
                    scopes: ['billing:refunds'],
                );
            }

            public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
            {
                return new ExecutionResultDTO(successful: true);
            }
        };

        app(ToolRegistryInterface::class)->register($tool);

        $this->expectException(AuthorizationFailedException::class);

        try {
            app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
                toolName: 'billing.refunds',
                parameters: ['invoice_id' => 10],
                context: new ExecutionContextDTO(
                    user: new AuthenticatedUserDTO(
                        id: $user->id,
                        name: $user->name,
                        guard: 'web',
                        abilities: ['reports:generate'],
                    ),
                ),
            ));
        } finally {
            Event::assertDispatched(MCPToolExecutionFailed::class);
        }
    }

    public function test_executor_dispatches_failure_event_when_tool_execution_throws(): void
    {
        Event::fake();

        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();
        $tool = new class implements ToolInterface
        {
            public function metadata(): ToolMetadataDTO
            {
                return new ToolMetadataDTO(
                    name: 'reports.fail',
                    description: 'Throw an exception.',
                    enabled: true,
                    scopes: ['reports:generate'],
                );
            }

            public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
            {
                throw new \RuntimeException('Tool execution exploded.');
            }
        };

        app(ToolRegistryInterface::class)->register($tool);

        $this->expectException(\RuntimeException::class);

        try {
            app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
                toolName: 'reports.fail',
                parameters: ['account_id' => 10],
                context: new ExecutionContextDTO(
                    user: new AuthenticatedUserDTO(
                        id: $user->id,
                        name: $user->name,
                        guard: 'web',
                        abilities: ['reports:generate'],
                    ),
                ),
            ));
        } finally {
            Event::assertDispatched(MCPToolExecutionFailed::class);
        }
    }
}

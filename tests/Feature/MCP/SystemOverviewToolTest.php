<?php

namespace Tests\Feature\MCP;

use App\Events\MCPToolExecuted;
use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\Contracts\ToolRegistryInterface;
use App\MCP\DTO\AuthenticatedUserDTO;
use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ToolRequestDTO;
use App\MCP\Exceptions\AuthorizationFailedException;
use App\MCP\Exceptions\InvalidToolRequestException;
use App\MCP\Exceptions\ToolDisabledException;
use App\MCP\Tools\SystemOverviewTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SystemOverviewToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_overview_tool_is_registered_from_configuration(): void
    {
        $tool = app(ToolRegistryInterface::class)->resolve('system.overview');

        $this->assertInstanceOf(SystemOverviewTool::class, $tool);
        $this->assertSame('system.overview', $tool->metadata()->name);
    }

    public function test_system_overview_tool_executes_through_the_pipeline(): void
    {
        Event::fake();

        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();

        $response = app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
            toolName: 'system.overview',
            parameters: ['include_stats' => true],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $user->id,
                    name: $user->name,
                    guard: 'web',
                    abilities: ['system:overview'],
                ),
                requestId: 'req-system-overview',
            ),
        ));

        $this->assertTrue($response->successful);
        $this->assertSame($user->id, $response->result->payload['request']['requested_by']);
        $this->assertSame(config('app.name'), $response->result->payload['server']['application']);
        $this->assertSame(config('mcp.default_provider'), $response->result->payload['server']['default_provider']);
        $this->assertSame(1, $response->result->payload['stats']['user_count']);

        Event::assertDispatched(MCPToolExecuted::class);
    }

    public function test_system_overview_tool_requires_the_correct_scope(): void
    {
        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();

        $this->expectException(AuthorizationFailedException::class);

        app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
            toolName: 'system.overview',
            parameters: ['include_stats' => true],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $user->id,
                    name: $user->name,
                    guard: 'web',
                    abilities: ['reports:generate'],
                ),
            ),
        ));
    }

    public function test_system_overview_tool_rejects_invalid_input(): void
    {
        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();

        $this->expectException(InvalidToolRequestException::class);

        app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
            toolName: 'system.overview',
            parameters: ['include_stats' => 'yes'],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $user->id,
                    name: $user->name,
                    guard: 'web',
                    abilities: ['system:overview'],
                ),
            ),
        ));
    }

    public function test_system_overview_tool_is_blocked_when_the_server_feature_is_disabled(): void
    {
        config()->set('mcp.feature_flags.mcp-server', false);

        $user = User::factory()->create();

        $this->expectException(ToolDisabledException::class);

        app(ToolExecutorInterface::class)->execute(new ToolRequestDTO(
            toolName: 'system.overview',
            parameters: ['include_stats' => true],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $user->id,
                    name: $user->name,
                    guard: 'web',
                    abilities: ['system:overview'],
                ),
            ),
        ));
    }
}

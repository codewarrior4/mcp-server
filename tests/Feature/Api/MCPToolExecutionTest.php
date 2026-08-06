<?php

namespace Tests\Feature\Api;

use App\Events\MCPToolExecutionFailed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MCPToolExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_request_can_execute_a_real_mcp_tool(): void
    {
        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['system:overview']);

        $this->postJson(route('mcp.execute'), [
            'tool_name' => 'system.overview',
            'parameters' => [
                'include_stats' => true,
            ],
            'request_id' => 'req-api-system-overview',
            'metadata' => [
                'source' => 'api-test',
            ],
        ])->assertOk()
            ->assertJson([
                'tool_name' => 'system.overview',
                'successful' => true,
                'request_id' => 'req-api-system-overview',
            ])
            ->assertJsonPath('payload.request.request_id', 'req-api-system-overview')
            ->assertJsonPath('payload.request.requested_by', $user->id)
            ->assertJsonPath('payload.stats.user_count', 1);
    }

    public function test_mcp_execute_endpoint_requires_authentication(): void
    {
        $this->postJson(route('mcp.execute'), [
            'tool_name' => 'system.overview',
            'parameters' => [
                'include_stats' => true,
            ],
        ])->assertUnauthorized();
    }

    public function test_mcp_execute_endpoint_validates_the_request_payload(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['system:overview']);

        $this->postJson(route('mcp.execute'), [
            'parameters' => 'not-an-array',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'tool_name',
                'parameters',
            ]);
    }

    public function test_mcp_execute_endpoint_returns_tool_validation_errors(): void
    {
        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['system:overview']);

        $this->postJson(route('mcp.execute'), [
            'tool_name' => 'system.overview',
            'parameters' => [
                'include_stats' => 'yes',
            ],
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'The tool request is invalid.')
            ->assertJsonPath('errors.include_stats.0', 'The include stats field must be true or false.');
    }

    public function test_mcp_execute_endpoint_returns_forbidden_for_missing_ability(): void
    {
        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['reports:generate']);

        $this->postJson(route('mcp.execute'), [
            'tool_name' => 'system.overview',
            'parameters' => [
                'include_stats' => true,
            ],
        ])->assertForbidden()
            ->assertJson([
                'message' => 'The authenticated user is missing the required tool scope.',
            ]);
    }

    public function test_mcp_execute_endpoint_reports_disabled_server_state(): void
    {
        config()->set('mcp.feature_flags.mcp-server', false);

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['system:overview']);

        $this->postJson(route('mcp.execute'), [
            'tool_name' => 'system.overview',
            'parameters' => [
                'include_stats' => true,
            ],
        ])->assertStatus(503);
    }

    public function test_mcp_execute_endpoint_reports_unknown_tools(): void
    {
        config()->set('mcp.feature_flags.mcp-server', true);

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['system:overview']);

        $this->postJson(route('mcp.execute'), [
            'tool_name' => 'system.missing',
            'parameters' => [
                'include_stats' => true,
            ],
        ])->assertNotFound()
            ->assertJson([
                'tool' => 'system.missing',
            ]);
    }

    public function test_mcp_execute_endpoint_dispatches_failure_event_with_request_context(): void
    {
        Event::fake();

        config()->set('mcp.feature_flags.mcp-server', false);

        $user = User::factory()->create();
        Sanctum::actingAs($user, ['system:overview']);

        $this->postJson(route('mcp.execute'), [
            'tool_name' => 'system.overview',
            'parameters' => [
                'include_stats' => true,
            ],
            'request_id' => 'req-api-disabled',
        ])->assertStatus(503);

        Event::assertDispatched(MCPToolExecutionFailed::class, function ($event): bool {
            return $event->request->toolName === 'system.overview'
                && $event->request->context->requestId === 'req-api-disabled'
                && $event->context()['request_id'] === 'req-api-disabled';
        });
    }
}

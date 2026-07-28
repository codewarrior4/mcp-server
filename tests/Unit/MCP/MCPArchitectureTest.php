<?php

namespace Tests\Unit\MCP;

use App\MCP\Actions\RecordAuditEvent;
use App\MCP\Actions\RegisterConfiguredTools;
use App\MCP\Contracts\AuditLoggerInterface;
use App\MCP\Contracts\AuthorizationInterface;
use App\MCP\Contracts\FeatureFlagManagerInterface;
use App\MCP\Contracts\PromptValidatorInterface;
use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\Contracts\ToolInterface;
use App\MCP\Contracts\ToolRegistryInterface;
use App\MCP\DTO\AuditEventDTO;
use App\MCP\DTO\AuthenticatedUserDTO;
use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ExecutionResultDTO;
use App\MCP\DTO\ToolMetadataDTO;
use App\MCP\DTO\ToolRequestDTO;
use App\MCP\Exceptions\InvalidToolConfigurationException;
use App\MCP\Exceptions\ToolAlreadyRegisteredException;
use App\MCP\Exceptions\ToolNotFoundException;
use App\Models\User;
use App\Providers\MCPServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MCPArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_mcp_config_exposes_expected_defaults(): void
    {
        $this->assertSame('openai', config('mcp.default_provider'));
        $this->assertSame(15, config('mcp.tool_timeout'));
        $this->assertTrue(config('mcp.audit_enabled'));
        $this->assertIsArray(config('mcp.feature_flags'));
        $this->assertIsString(config('mcp.audit.channel'));
    }

    public function test_mcp_contracts_resolve_from_the_container(): void
    {
        $this->assertContains(MCPServiceProvider::class, require base_path('bootstrap/providers.php'));
        $this->assertInstanceOf(ToolRegistryInterface::class, app(ToolRegistryInterface::class));
        $this->assertInstanceOf(AuthorizationInterface::class, app(AuthorizationInterface::class));
        $this->assertInstanceOf(AuditLoggerInterface::class, app(AuditLoggerInterface::class));
        $this->assertInstanceOf(FeatureFlagManagerInterface::class, app(FeatureFlagManagerInterface::class));
        $this->assertInstanceOf(PromptValidatorInterface::class, app(PromptValidatorInterface::class));
        $this->assertInstanceOf(ToolExecutorInterface::class, app(ToolExecutorInterface::class));
    }

    public function test_dtos_are_immutable_readonly_objects(): void
    {
        $request = new ToolRequestDTO(
            toolName: 'reports.generate',
            parameters: ['account_id' => 1],
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: 1,
                    name: 'Taylor Otwell',
                    guard: 'sanctum',
                ),
            ),
        );

        $this->assertSame('reports.generate', $request->toolName);
        $this->assertSame(1, $request->parameters['account_id']);
        $this->assertSame('Taylor Otwell', $request->context->user->name);
    }

    public function test_authorization_allows_tools_when_a_required_scope_is_present(): void
    {
        $user = User::factory()->create();
        $authorizer = app(AuthorizationInterface::class);

        $response = $authorizer->authorize(
            new ToolMetadataDTO(
                name: 'reports.generate',
                description: 'Generate a report.',
                enabled: true,
                scopes: ['reports:generate'],
            ),
            new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $user->id,
                    name: $user->name,
                    guard: 'web',
                    abilities: ['reports:generate'],
                ),
            ),
        );

        $this->assertTrue($response->allowed());
    }

    public function test_authorization_denies_tools_when_the_scope_is_missing(): void
    {
        $user = User::factory()->create();
        $authorizer = app(AuthorizationInterface::class);

        $response = $authorizer->authorize(
            new ToolMetadataDTO(
                name: 'billing.refunds',
                description: 'Issue a customer refund.',
                enabled: true,
                scopes: ['billing:refunds'],
            ),
            new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $user->id,
                    name: $user->name,
                    guard: 'web',
                    abilities: ['reports:generate'],
                ),
            ),
        );

        $this->assertFalse($response->allowed());
        $this->assertSame('The authenticated user is missing the required tool scope.', $response->message());
    }

    public function test_audit_event_exposes_log_context(): void
    {
        $event = new AuditEventDTO(
            toolName: 'reports.generate',
            userId: 7,
            parameters: ['account_id' => 99],
            successful: false,
            recordedAt: CarbonImmutable::parse('2026-07-28T10:00:00-07:00'),
            ipAddress: '127.0.0.1',
            durationInMilliseconds: 450,
            failureReason: 'Timed out.',
        );

        $this->assertSame([
            'tool_name' => 'reports.generate',
            'user_id' => 7,
            'parameters' => ['account_id' => 99],
            'successful' => false,
            'recorded_at' => '2026-07-28T10:00:00-07:00',
            'ip_address' => '127.0.0.1',
            'duration_in_milliseconds' => 450,
            'failure_reason' => 'Timed out.',
        ], $event->toLogContext());
    }

    public function test_record_audit_event_logs_when_auditing_is_enabled(): void
    {
        config()->set('mcp.audit_enabled', true);
        $fakeLogger = new class implements AuditLoggerInterface
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

        (new RecordAuditEvent($fakeLogger))->handle(new AuditEventDTO(
            toolName: 'reports.generate',
            userId: 3,
            parameters: ['account_id' => 1],
            successful: true,
            recordedAt: CarbonImmutable::parse('2026-07-28T11:00:00-07:00'),
        ));

        $this->assertCount(1, $fakeLogger->events);
        $this->assertSame('reports.generate', $fakeLogger->events[0]->toolName);
    }

    public function test_record_audit_event_skips_logging_when_auditing_is_disabled(): void
    {
        config()->set('mcp.audit_enabled', false);
        $fakeLogger = new class implements AuditLoggerInterface
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

        (new RecordAuditEvent($fakeLogger))->handle(new AuditEventDTO(
            toolName: 'reports.generate',
            userId: 3,
            parameters: ['account_id' => 1],
            successful: true,
            recordedAt: CarbonImmutable::parse('2026-07-28T11:00:00-07:00'),
        ));

        $this->assertCount(0, $fakeLogger->events);
    }

    public function test_registry_resolves_registered_tools(): void
    {
        $registry = app(ToolRegistryInterface::class);
        $tool = new class implements ToolInterface
        {
            public function metadata(): ToolMetadataDTO
            {
                return new ToolMetadataDTO(
                    name: 'reports.generate',
                    description: 'Generate a report.',
                    enabled: false,
                    scopes: ['reports:generate'],
                );
            }

            public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
            {
                return new ExecutionResultDTO(successful: true);
            }
        };

        $registry->register($tool);

        $resolved = $registry->resolve('reports.generate');

        $this->assertSame($tool, $resolved);
        $this->assertFalse($registry->all()[0]->enabled);
    }

    public function test_registry_prevents_duplicate_tool_registration(): void
    {
        $registry = app(ToolRegistryInterface::class);
        $tool = new class implements ToolInterface
        {
            public function metadata(): ToolMetadataDTO
            {
                return new ToolMetadataDTO(
                    name: 'reports.generate',
                    description: 'Generate a report.',
                );
            }

            public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
            {
                return new ExecutionResultDTO(successful: true);
            }
        };

        $registry->register($tool);

        $this->expectException(ToolAlreadyRegisteredException::class);

        $registry->register($tool);
    }

    public function test_registry_throws_when_resolving_an_unknown_tool(): void
    {
        $this->expectException(ToolNotFoundException::class);

        app(ToolRegistryInterface::class)->resolve('missing.tool');
    }

    public function test_registry_can_enable_and_disable_tools(): void
    {
        $registry = app(ToolRegistryInterface::class);
        $tool = new class implements ToolInterface
        {
            public function metadata(): ToolMetadataDTO
            {
                return new ToolMetadataDTO(
                    name: 'billing.refunds',
                    description: 'Issue a refund.',
                );
            }

            public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
            {
                return new ExecutionResultDTO(successful: true);
            }
        };

        $registry->register($tool);
        $registry->enable('billing.refunds');

        $enabledMetadata = $registry->all()[0];

        $registry->disable('billing.refunds');

        $disabledMetadata = $registry->all()[0];

        $this->assertTrue($enabledMetadata->enabled);
        $this->assertFalse($disabledMetadata->enabled);
    }

    public function test_configured_tool_registration_loads_unique_tools(): void
    {
        $registry = app(ToolRegistryInterface::class);
        $toolClass = new class implements ToolInterface
        {
            public function metadata(): ToolMetadataDTO
            {
                return new ToolMetadataDTO(
                    name: 'analytics.summary',
                    description: 'Fetch analytics summary.',
                );
            }

            public function execute(array $parameters, ExecutionContextDTO $context): ExecutionResultDTO
            {
                return new ExecutionResultDTO(successful: true);
            }
        };

        $className = $toolClass::class;
        app()->instance($className, $toolClass);

        config()->set('mcp.tool_discovery.tools', [$className, $className]);

        app(RegisterConfiguredTools::class)->handle($registry);

        $this->assertCount(1, $registry->all());
        $this->assertSame('analytics.summary', $registry->all()[0]->name);
    }

    public function test_configured_tool_registration_rejects_invalid_tools(): void
    {
        $registry = app(ToolRegistryInterface::class);
        $className = 'tests.fake.invalid-tool';

        app()->bind($className, fn (): object => new \stdClass);
        config()->set('mcp.tool_discovery.tools', [$className]);

        $this->expectException(InvalidToolConfigurationException::class);

        app(RegisterConfiguredTools::class)->handle($registry);
    }

    public function test_feature_flags_start_disabled_by_default(): void
    {
        $featureFlags = app(FeatureFlagManagerInterface::class);

        $this->assertFalse($featureFlags->enabled('mcp-server'));
        $this->assertFalse($featureFlags->enabled('tool-registry'));
        $this->assertFalse($featureFlags->enabled('audit-log'));
    }
}

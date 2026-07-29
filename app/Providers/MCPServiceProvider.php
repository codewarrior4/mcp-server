<?php

namespace App\Providers;

use App\MCP\Actions\RegisterConfiguredTools;
use App\MCP\Contracts\AuditLoggerInterface;
use App\MCP\Contracts\AuthenticatedUserResolverInterface;
use App\MCP\Contracts\AuthorizationInterface;
use App\MCP\Contracts\FeatureFlagManagerInterface;
use App\MCP\Contracts\PromptValidatorInterface;
use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\Contracts\ToolRegistryInterface;
use App\MCP\Services\ConfigFeatureFlagManager;
use App\MCP\Services\EloquentAuthenticatedUserResolver;
use App\MCP\Services\ExecutionPipelineToolExecutor;
use App\MCP\Services\GateAuthorizer;
use App\MCP\Services\InMemoryToolRegistry;
use App\MCP\Services\LogAuditLogger;
use App\MCP\Services\PromptValidator;
use Illuminate\Support\ServiceProvider;

class MCPServiceProvider extends ServiceProvider
{
    public $bindings = [
        AuthenticatedUserResolverInterface::class => EloquentAuthenticatedUserResolver::class,
        AuthorizationInterface::class => GateAuthorizer::class,
        AuditLoggerInterface::class => LogAuditLogger::class,
        FeatureFlagManagerInterface::class => ConfigFeatureFlagManager::class,
        PromptValidatorInterface::class => PromptValidator::class,
        ToolExecutorInterface::class => ExecutionPipelineToolExecutor::class,
    ];

    public $singletons = [
        ToolRegistryInterface::class => InMemoryToolRegistry::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/mcp.php', 'mcp');
    }

    public function boot(RegisterConfiguredTools $registerConfiguredTools): void
    {
        $registerConfiguredTools->handle($this->app->make(ToolRegistryInterface::class));
    }
}

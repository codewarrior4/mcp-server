<?php

namespace App\Providers;

use App\Events\MCPToolExecutionFailed;
use App\MCP\Enums\FeatureFlag;
use App\MCP\Policies\ToolExecutionPolicy;
use App\Models\User;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Events\UnknownFeatureResolved;
use Laravel\Pennant\Feature;
use Laravel\Pulse\Entry;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Value;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('execute-mcp-tool', [ToolExecutionPolicy::class, 'execute']);
        Gate::define('viewPulse', fn (?User $user): bool => $this->canViewInternalDashboard($user));

        foreach (FeatureFlag::cases() as $featureFlag) {
            Feature::define($featureFlag->value, fn (): bool => (bool) config("mcp.feature_flags.{$featureFlag->value}", false));
        }

        Event::listen(function (UnknownFeatureResolved $event): void {
            report(new \RuntimeException("Unknown Pennant feature resolved: {$event->feature}"));
        });

        Pulse::filter(function (Entry|Value $entry): bool {
            return ! str_contains((string) $entry->type, 'telescope');
        });

        Pulse::handleExceptionsUsing(function (Throwable $exception): void {
            report($exception);
        });

        Event::listen(function (MCPToolExecutionFailed $event): void {
            Log::warning('MCP tool execution failed.', [
                'tool_name' => $event->request->toolName,
                'user_id' => $event->request->context->user->id,
                'duration_in_milliseconds' => $event->durationInMilliseconds,
                'message' => $event->exception->getMessage(),
            ]);
        });

        Queue::before(function (JobProcessing $event): void {
            Log::info('Queue job processing started.', [
                'connection' => $event->connectionName,
                'job' => $event->job->resolveName(),
            ]);
        });

        Queue::after(function (JobProcessed $event): void {
            Log::info('Queue job processed successfully.', [
                'connection' => $event->connectionName,
                'job' => $event->job->resolveName(),
            ]);
        });

        Queue::failing(function (JobFailed $event): void {
            Log::error('Queue job failed.', [
                'connection' => $event->connectionName,
                'job' => $event->job->resolveName(),
                'message' => $event->exception->getMessage(),
            ]);
        });
    }

    private function canViewInternalDashboard(?User $user): bool
    {
        if ($this->app->environment('local')) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        $allowedEmails = array_filter(array_map('trim', explode(',', (string) env('INTERNAL_DASHBOARD_EMAILS', ''))));

        return in_array($user->email, $allowedEmails, true);
    }
}

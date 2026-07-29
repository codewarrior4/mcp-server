<?php

namespace App\Providers;

use App\MCP\Enums\FeatureFlag;
use App\MCP\Policies\ToolExecutionPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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

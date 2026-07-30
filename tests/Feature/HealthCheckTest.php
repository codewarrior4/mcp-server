<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_index_returns_json_status(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(in_array($response->status(), [200, 503], true) ? $response->status() : 503)
            ->assertJsonStructure([
                'status',
                'checks' => [
                    'database' => ['ok', 'driver'],
                    'cache' => ['ok', 'driver'],
                    'queue' => ['ok', 'driver'],
                    'redis' => ['ok', 'driver'],
                ],
            ]);
    }

    public function test_database_health_endpoint_returns_ok(): void
    {
        $this->getJson('/health/database')
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'driver' => config('database.default'),
            ]);
    }

    public function test_redis_health_endpoint_can_report_degraded_state(): void
    {
        $response = $this->getJson('/health/redis');

        $response->assertStatus(in_array($response->status(), [200, 503], true) ? $response->status() : 503)
            ->assertJsonStructure([
                'ok',
                'driver',
            ]);
    }
}

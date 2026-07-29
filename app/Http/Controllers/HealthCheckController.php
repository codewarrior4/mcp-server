<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthCheckController extends Controller
{
    public function index(): JsonResponse
    {
        $checks = [
            'database' => $this->databaseStatus(),
            'cache' => $this->cacheStatus(),
            'queue' => $this->queueStatus(),
            'redis' => $this->redisStatus(),
        ];

        $healthy = collect($checks)
            ->pluck('ok')
            ->every(static fn (bool $ok): bool => $ok);

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    public function database(): JsonResponse
    {
        $status = $this->databaseStatus();

        return response()->json($status, $status['ok'] ? 200 : 503);
    }

    public function cache(): JsonResponse
    {
        $status = $this->cacheStatus();

        return response()->json($status, $status['ok'] ? 200 : 503);
    }

    public function queue(): JsonResponse
    {
        $status = $this->queueStatus();

        return response()->json($status, $status['ok'] ? 200 : 503);
    }

    public function redis(): JsonResponse
    {
        $status = $this->redisStatus();

        return response()->json($status, $status['ok'] ? 200 : 503);
    }

    /**
     * @return array{ok: bool, driver: string, message?: string}
     */
    private function databaseStatus(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'ok' => true,
                'driver' => (string) config('database.default'),
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'driver' => (string) config('database.default'),
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{ok: bool, driver: string, message?: string}
     */
    private function cacheStatus(): array
    {
        try {
            $key = 'health-check:'.uniqid('', true);
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'ok' => $value === 'ok',
                'driver' => (string) config('cache.default'),
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'driver' => (string) config('cache.default'),
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{ok: bool, driver: string, message?: string}
     */
    private function queueStatus(): array
    {
        try {
            $connectionName = (string) config('queue.default');
            $connection = Queue::connection($connectionName);

            return [
                'ok' => method_exists($connection, 'size'),
                'driver' => $connectionName,
                'message' => method_exists($connection, 'size') ? null : 'Queue connection does not expose size inspection.',
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'driver' => (string) config('queue.default'),
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{ok: bool, driver: string, message?: string}
     */
    private function redisStatus(): array
    {
        try {
            Redis::connection()->ping();

            return [
                'ok' => true,
                'driver' => 'redis',
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'driver' => 'redis',
                'message' => $exception->getMessage(),
            ];
        }
    }
}

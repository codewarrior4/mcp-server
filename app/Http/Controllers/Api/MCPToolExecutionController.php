<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExecuteMCPToolRequest;
use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\Contracts\ToolRegistryInterface;
use App\MCP\DTO\AuthenticatedUserDTO;
use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ToolRequestDTO;
use App\MCP\Exceptions\AuthorizationFailedException;
use App\MCP\Exceptions\InvalidToolRequestException;
use App\MCP\Exceptions\ToolDisabledException;
use App\MCP\Exceptions\ToolNotFoundException;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Throwable;

class MCPToolExecutionController extends Controller
{
    public function __invoke(
        ExecuteMCPToolRequest $request,
        ToolExecutorInterface $toolExecutor,
        ToolRegistryInterface $toolRegistry,
    ): JsonResponse {
        try {
            $response = $toolExecutor->execute($this->buildToolRequest($request, $toolRegistry));

            return response()->json([
                'tool_name' => $response->toolName,
                'successful' => $response->successful,
                'payload' => $response->result->payload,
                'message' => $response->result->message,
                'request_id' => $request->validated('request_id'),
                'duration_in_milliseconds' => $response->durationInMilliseconds,
            ]);
        } catch (InvalidToolRequestException $exception) {
            return response()->json($exception->toArray(), 422);
        } catch (AuthorizationFailedException $exception) {
            return response()->json($exception->toArray(), 403);
        } catch (ToolNotFoundException $exception) {
            return response()->json($exception->toArray(), 404);
        } catch (ToolDisabledException $exception) {
            return response()->json($exception->toArray(), 503);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Tool execution failed unexpectedly.',
            ], 500);
        }
    }

    private function buildToolRequest(ExecuteMCPToolRequest $request, ToolRegistryInterface $toolRegistry): ToolRequestDTO
    {
        /** @var User $user */
        $user = $request->user();
        $toolName = $request->validated('tool_name');

        return new ToolRequestDTO(
            toolName: $toolName,
            parameters: $request->validated('parameters'),
            context: new ExecutionContextDTO(
                user: new AuthenticatedUserDTO(
                    id: $user->getAuthIdentifier(),
                    name: (string) $user->name,
                    guard: 'sanctum',
                    abilities: $this->resolveTokenAbilities($user, $toolName, $toolRegistry),
                ),
                ipAddress: $request->ip(),
                requestId: $request->validated('request_id'),
                metadata: $request->validated('metadata', []),
            ),
        );
    }

    /**
     * @return array<int, string>
     */
    private function resolveTokenAbilities(User $user, string $toolName, ToolRegistryInterface $toolRegistry): array
    {
        if (! $toolRegistry->has($toolName)) {
            return [];
        }

        $toolMetadata = $toolRegistry->resolve($toolName)->metadata();
        $candidateAbilities = [
            'mcp:execute',
            'mcp:tool:'.$toolName,
            ...$toolMetadata->scopes,
        ];

        return array_values(array_filter(
            array_unique($candidateAbilities),
            fn (string $ability): bool => $user->tokenCan($ability),
        ));
    }
}

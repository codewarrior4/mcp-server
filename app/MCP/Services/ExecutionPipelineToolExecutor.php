<?php

namespace App\MCP\Services;

use App\Events\MCPToolExecuted;
use App\MCP\Actions\RecordAuditEvent;
use App\MCP\Contracts\AuthorizationInterface;
use App\MCP\Contracts\FeatureFlagManagerInterface;
use App\MCP\Contracts\PromptValidatorInterface;
use App\MCP\Contracts\ToolExecutorInterface;
use App\MCP\Contracts\ToolRegistryInterface;
use App\MCP\DTO\AuditEventDTO;
use App\MCP\DTO\ToolRequestDTO;
use App\MCP\DTO\ToolResponseDTO;
use App\MCP\Enums\FeatureFlag;
use App\MCP\Exceptions\AuthorizationFailedException;
use App\MCP\Exceptions\ToolDisabledException;
use Carbon\CarbonImmutable;

class ExecutionPipelineToolExecutor implements ToolExecutorInterface
{
    public function __construct(
        private ToolRegistryInterface $toolRegistry,
        private ToolRequestValidator $validator,
        private AuthorizationInterface $authorizer,
        private PromptValidatorInterface $promptValidator,
        private RecordAuditEvent $recordAuditEvent,
        private FeatureFlagManagerInterface $featureFlags,
    ) {}

    public function execute(ToolRequestDTO $request): ToolResponseDTO
    {
        $startedAt = microtime(true);

        $this->validator->validate($request);

        if (! $this->featureFlags->enabled(FeatureFlag::MCPServer->value)) {
            throw new ToolDisabledException($request->toolName);
        }

        $tool = $this->toolRegistry->resolve($request->toolName);
        $toolMetadata = collect($this->toolRegistry->all())
            ->firstWhere('name', $request->toolName) ?? $tool->metadata();

        if (! $toolMetadata->enabled) {
            throw new ToolDisabledException($request->toolName);
        }

        if (array_key_exists('prompt', $request->parameters) && is_string($request->parameters['prompt'])) {
            $this->promptValidator->ensureSafe($request->parameters['prompt']);
        }

        $authorization = $this->authorizer->authorize($toolMetadata, $request->context);

        if (! $authorization->allowed()) {
            throw new AuthorizationFailedException(
                toolName: $request->toolName,
                message: $authorization->message() ?: 'You are not authorized to execute this tool.',
            );
        }

        $result = $tool->execute($request->parameters, $request->context);
        $durationInMilliseconds = (int) round((microtime(true) - $startedAt) * 1000);

        $response = new ToolResponseDTO(
            toolName: $request->toolName,
            successful: $result->successful,
            result: $result,
            durationInMilliseconds: $durationInMilliseconds,
        );

        $this->recordAuditEvent->handle(new AuditEventDTO(
            toolName: $request->toolName,
            userId: $request->context->user->id,
            parameters: $request->parameters,
            successful: $result->successful,
            recordedAt: CarbonImmutable::now(),
            ipAddress: $request->context->ipAddress,
            durationInMilliseconds: $durationInMilliseconds,
            failureReason: $result->successful ? null : $result->message,
        ));

        MCPToolExecuted::dispatch($response);

        return $response;
    }
}

<?php

namespace App\MCP\DTO;

use Carbon\CarbonImmutable;

final readonly class AuditEventDTO
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        public string $toolName,
        public int|string $userId,
        public array $parameters,
        public bool $successful,
        public CarbonImmutable $recordedAt,
        public ?string $ipAddress = null,
        public ?int $durationInMilliseconds = null,
        public ?string $failureReason = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toLogContext(): array
    {
        return [
            'tool_name' => $this->toolName,
            'user_id' => $this->userId,
            'parameters' => $this->parameters,
            'successful' => $this->successful,
            'recorded_at' => $this->recordedAt->toIso8601String(),
            'ip_address' => $this->ipAddress,
            'duration_in_milliseconds' => $this->durationInMilliseconds,
            'failure_reason' => $this->failureReason,
        ];
    }
}

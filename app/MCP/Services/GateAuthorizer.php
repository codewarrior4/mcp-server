<?php

namespace App\MCP\Services;

use App\MCP\Contracts\AuthenticatedUserResolverInterface;
use App\MCP\Contracts\AuthorizationInterface;
use App\MCP\DTO\ExecutionContextDTO;
use App\MCP\DTO\ToolMetadataDTO;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class GateAuthorizer implements AuthorizationInterface
{
    public function __construct(private AuthenticatedUserResolverInterface $userResolver) {}

    public function authorize(ToolMetadataDTO $tool, ExecutionContextDTO $context): Response
    {
        $user = $this->userResolver->resolve($context->user);

        if ($user === null) {
            return Response::deny('The authenticated user could not be resolved.');
        }

        return Gate::forUser($user)->inspect('execute-mcp-tool', [$tool, $context->user->abilities]);
    }
}

<?php

namespace App\MCP\Policies;

use App\MCP\DTO\ToolMetadataDTO;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ToolExecutionPolicy
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function execute(User $user, ToolMetadataDTO $tool, array $abilities = []): Response
    {
        if (! $tool->enabled) {
            return Response::deny('This tool is currently disabled.');
        }

        if ($tool->scopes === []) {
            return Response::allow();
        }

        $grantedAbilities = array_unique([
            ...$abilities,
            'mcp:execute',
            'mcp:tool:'.$tool->name,
        ]);

        foreach ($tool->scopes as $scope) {
            if (in_array($scope, $grantedAbilities, true)) {
                return Response::allow();
            }
        }

        return Response::deny('The authenticated user is missing the required tool scope.');
    }
}

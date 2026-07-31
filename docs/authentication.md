# Authentication

## Why

Tool execution should always happen on behalf of a known actor. Authentication gives the rest of the pipeline something real to authorize and audit.

## What Exists Now

- Laravel Sanctum is installed as the authentication foundation.
- Execution context carries authenticated user identity and abilities.
- Authorization decisions use the authenticated user inside the request context.
- Internal dashboards can be restricted by allowed email addresses outside local environments.

## How It Works

- User identity is represented with `AuthenticatedUserDTO`.
- Request-level context is wrapped in `ExecutionContextDTO`.
- Authorization checks read the user's declared abilities before tool execution.
- Audit events store the acting user ID for traceability.

## Current Assumptions

- Requests reaching the execution pipeline already have a trusted authenticated user.
- Ability data is available at execution time.
- Local development remains more permissive for dashboards.

## Risks

- The public-facing MCP transport layer is not implemented yet, so final request authentication still needs to be wired end to end.
- Ability hydration strategy may need revision once real external clients are added.

## Next Improvements

- Add an authenticated MCP entrypoint that resolves the current user automatically.
- Define token issuance and revocation rules for tool consumers.
- Document ability naming and scope ownership per tool family.

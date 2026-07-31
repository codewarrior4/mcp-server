# Security

## Why

An MCP server sits between AI systems and application capabilities. That makes security a design requirement, not a later enhancement.

## What Exists Now

- Tool execution is routed through a single execution pipeline.
- Every request is validated before tool lookup and execution.
- Authorization is checked against declared tool scopes.
- Global MCP access is guarded by a feature flag.
- Individual tools can be disabled through metadata.
- Tool executions are audited.
- Internal dashboards are restricted outside local environments.

## How It Works

1. A request is transformed into a `ToolRequestDTO`.
2. The validator rejects malformed tool names or invalid payloads.
3. The MCP server feature flag is checked before continuing.
4. The requested tool is resolved from the registry.
5. Tool metadata is used to verify the tool is enabled.
6. Prompt-like input is passed through the prompt validator when present.
7. Authorization verifies the authenticated user has the required abilities.
8. Execution success and failures are both recorded as audit events.

## Current Mitigations

- Centralized execution path reduces the chance of bypassing checks.
- Feature flags allow rollout without exposing the system broadly.
- Audit logging creates a trail for investigation.
- Gate-based authorization keeps access rules inside Laravel conventions.

## Risks Still Open

- Tool implementations themselves still need careful input validation.
- There is no tenant isolation layer yet.
- Secret rotation and provider credential lifecycle are not implemented.
- Rate limiting and abuse controls are not yet part of the public surface.

## Next Improvements

- Add rate limiting around MCP-facing endpoints.
- Introduce stronger per-tool parameter schemas.
- Add organization or team-aware authorization rules.
- Prepare secure secret rotation and provider credential review.

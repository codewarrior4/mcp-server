# Threat Model

## Main Risks

### Unauthorized Tool Execution

Risk:
Requests attempt to execute tools without the required ability.

Mitigation:
- Sanctum authentication
- scope-based authorization
- centralized execution pipeline

### Direct Tool Exposure

Risk:
Business logic gets called without consistent validation or audit checks.

Mitigation:
- tool execution is routed through a single pipeline
- tool registration is controlled

### Silent Failures

Risk:
Execution failures happen without enough context to debug them.

Mitigation:
- audit logging
- failure events
- request correlation through `request_id`

### Unsafe Rollout

Risk:
A partially finished MCP surface becomes available too early.

Mitigation:
- Pennant feature flags
- tool-level enable / disable controls

### Unknown Tool Requests

Risk:
Clients call tools that do not exist or are not configured.

Mitigation:
- registry resolution
- predictable `404` JSON response

## Remaining Risks

- broader tenant or organization isolation is not implemented
- rate limiting is not yet applied to the MCP endpoint
- prompt safety is minimal and should be expanded before larger tool surfaces are added

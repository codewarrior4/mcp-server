# Observability

## Goal

The MCP server should make it easy to answer:

- Is the application healthy?
- Are tool executions succeeding?
- Are queue jobs backing up?
- Can we see failures early?

## Current Signals

- `/health`
- `/health/database`
- `/health/cache`
- `/health/queue`
- `/health/redis`
- MCP audit logs
- `MCPToolExecuted` events
- `MCPToolExecutionFailed` events
- Horizon dashboard
- Telescope dashboard
- Pulse dashboard

## Metrics That Matter

- MCP tool execution count
- MCP tool failure count
- average tool duration
- slowest tools
- queue backlog
- queue failure count
- Horizon wait time
- authorization failures
- feature flag usage

## Alert Candidates

- `/health` returns `503`
- Redis becomes unavailable
- queue failures spike
- MCP tool failures spike
- Horizon wait time grows beyond threshold

## Log-Only Signals

- successful tool execution
- queue start / finish events
- normal feature flag checks

## Current Gaps

- Horizon is installed, but production use still depends on real Redis infrastructure.
- Pulse is wired, but local SQLite is not a strong production observability backend.
- External alert routing is not configured yet.

## Next Improvements

- add external error and alerting integration
- add per-tool slow execution thresholds
- benchmark Redis-backed queue processing

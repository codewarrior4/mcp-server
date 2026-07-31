# Logging

## Why

If tool execution fails, logging is the fastest way to understand what happened before deeper tracing exists.

## What Exists Now

- Audit events record tool name, user ID, parameters, duration, and failure reason.
- Failed MCP executions emit a warning log with execution context.
- Queue lifecycle hooks log job start, success, and failure.
- The audit logger writes through the configured logging channel.

## How It Works

- `RecordAuditEvent` sends structured audit data to the configured audit logger.
- `LogAuditLogger` writes log context through Laravel logging.
- `MCPToolExecutionFailed` listeners capture tool execution failures.
- Queue events provide operational visibility for asynchronous execution.

## What To Watch

- Repeated authorization failures
- Repeated disabled tool requests
- Tool execution duration increases
- Queue failures or long-running background jobs

## Current Gaps

- Logs are structured, but not yet correlated with a distributed trace ID.
- There is no external log shipping strategy documented yet.
- Alert thresholds are still operational guidance rather than enforced automation.

## Next Improvements

- Add request correlation IDs to every MCP-facing entrypoint.
- Forward logs to a centralized provider in production.
- Define alert rules for repeated failures and queue backlogs.

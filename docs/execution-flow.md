# Execution Flow

## Why

The execution pipeline is the core engineering decision in this repository. Everything important happens around it.

## Flow

1. Receive a tool request.
2. Build a `ToolRequestDTO`.
3. Validate request structure.
4. Verify the global MCP feature flag.
5. Resolve the tool from the registry.
6. Verify the tool is enabled.
7. Validate prompt-like input when present.
8. Authorize the user against the tool's required scopes.
9. Execute the tool.
10. Record an audit event.
11. Dispatch success or failure events.
12. Return a `ToolResponseDTO` or surface the exception.

## Why This Shape

- It creates one place to reason about security.
- It keeps tool implementations smaller.
- It makes failures observable.
- It supports queued execution without changing the domain contract.

## First Real Tool

The first real tool in this repository is `system.overview`.

It returns a safe operational summary of the MCP server and currently supports an `include_stats` flag to optionally include lightweight aggregate data.

## Failure Modes

- Invalid request
- Disabled MCP server
- Disabled tool
- Authorization failure
- Prompt validation failure
- Tool execution exception

Each failure still records audit context and emits a failure event.

## Future Improvements

- Add transport-level request handling around the pipeline.
- Add stronger typed schemas per tool.
- Add tracing spans around each pipeline stage.

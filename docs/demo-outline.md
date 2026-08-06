# Demo Outline

## Goal

Show the engineering decisions clearly in 8 to 10 minutes.

Do not try to make the project look bigger than it is.

## Flow

1. Explain the problem.
   AI should not call application logic directly.

2. Show the repository structure.
   Point out `app/MCP`, `routes/api.php`, `tests`, and `docs`.

3. Show the architecture note.
   Use `docs/architecture.md` or the diagrams in `diagrams/`.

4. Show the first real tool.
   `system.overview`

5. Show the API entrypoint.
   `POST /api/mcp/execute`

6. Show the execution pipeline.
   Highlight validation, feature flags, authorization, and audit behavior.

7. Show failure handling.
   Explain unauthorized access, disabled server behavior, and missing tools.

8. Show tests.
   API tests and pipeline tests are enough.

9. Show operational support.
   Health endpoints, Horizon, Pulse, Telescope, and CI.

## Best Files To Open

- `app/Http/Controllers/Api/MCPToolExecutionController.php`
- `app/MCP/Services/ExecutionPipelineToolExecutor.php`
- `app/MCP/Tools/SystemOverviewTool.php`
- `tests/Feature/Api/MCPToolExecutionTest.php`
- `docs/architecture.md`

## Best Demo Path

1. Show route
2. Show controller
3. Show tool
4. Show test proving success
5. Show test proving failure

## Keep It Honest

- one real tool
- one real execution path
- strong security and failure handling around a small surface

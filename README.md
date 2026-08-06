# MCP Server

This project is building a production-minded Laravel MCP server that exposes internal tools to AI systems through a controlled execution pipeline.

## What It Does

- validates every MCP request
- authorizes every tool execution
- records audit data
- supports queued execution
- keeps features behind flags
- exposes health and operational signals

## Current Stack

- Laravel 13
- PHP 8.4
- Sanctum
- Pennant
- Horizon
- Telescope
- Pulse
- SQLite for local development

## Current Engineering Surface

- MCP contracts and registry
- execution pipeline
- first real MCP tool: `system.overview`
- authenticated MCP API entry flow: `POST /api/mcp/execute`
- audit logging
- queue-ready MCP job execution
- health endpoints
- Horizon / Telescope / Pulse integration groundwork

## Local Setup

```bash
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Useful Commands

```bash
php artisan test
vendor/bin/pint --dirty --format agent
php artisan horizon
php artisan pulse:check
php artisan queue:work
```

## Health Endpoints

- `/health`
- `/health/database`
- `/health/cache`
- `/health/queue`
- `/health/redis`

## Operational Notes

- Horizon is installed, but production use should run on Redis-backed queues.
- Pulse is installed and usable, but production monitoring should not depend on local SQLite assumptions.
- Internal dashboard access outside local should be controlled through `INTERNAL_DASHBOARD_EMAILS`.

## CI

The GitHub workflow validates Composer, installs dependencies, runs migrations, checks code style, and executes tests on PHP 8.4.

## Examples

- [System Overview Request](examples/mcp-execute-system-overview.json)
- [Unauthorized Request Example](examples/mcp-execute-unauthorized.json)

## Demo Support

- [Architecture Notes](docs/architecture.md)
- [Demo Outline](docs/demo-outline.md)
- [Architecture Diagram](diagrams/architecture.md)
- [Sequence Diagram](diagrams/sequence.md)
- [Threat Model](diagrams/threat-model.md)

## Docs

- [Architecture](docs/architecture.md)
- [Security](docs/security.md)
- [Authentication](docs/authentication.md)
- [Logging](docs/logging.md)
- [Execution Flow](docs/execution-flow.md)
- [Observability](docs/observability.md)
- [Performance](docs/performance.md)
- [Production Checklist](docs/production-checklist.md)
- [Rollback](docs/rollback.md)
- [Week 01 Retrospective](docs/retrospective-week-01.md)

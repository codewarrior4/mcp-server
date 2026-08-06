# Architecture

## Purpose

This project is a Laravel MCP server built to expose internal tools through a controlled execution path instead of direct application access.

The architecture is intentionally small:

- one authenticated entry flow
- one execution pipeline
- a tool registry
- feature flags
- audit logging
- health and operational visibility

## Core Flow

1. An authenticated request reaches the MCP API endpoint.
2. The request is validated with a Laravel Form Request.
3. The controller maps the payload into MCP DTOs.
4. The execution pipeline validates the tool request.
5. The global MCP feature flag is checked.
6. The requested tool is resolved from the registry.
7. Tool-level authorization is evaluated.
8. The tool executes.
9. Audit data is recorded.
10. Success or failure events are dispatched.
11. A JSON response is returned to the caller.

## Main Components

### API Layer

- `POST /api/mcp/execute`
- Sanctum-protected
- request validation and request-to-DTO mapping

### MCP Contracts

- tool interface
- tool executor interface
- tool registry interface
- authorization and audit contracts

### Execution Pipeline

The pipeline is the main engineering boundary.

It handles:

- validation
- feature gating
- tool resolution
- authorization
- execution
- audit logging
- event dispatching

### Tool Registry

The registry provides a controlled way to register and resolve tools.

Current real tool:

- `system.overview`

### Security Controls

- Sanctum authentication
- scope-based authorization
- feature flags with Pennant
- structured audit events

### Operational Support

- health endpoints
- Horizon
- Pulse
- Telescope
- CI checks

## Why This Shape

- It avoids exposing business logic directly to AI-facing requests.
- It keeps tool implementations simpler than embedding security logic inside each tool.
- It makes failure behavior easier to observe and test.
- It gives the project a clean first version without pretending to solve every future problem.

## Current Limitations

- only one real tool exists today
- the public tool surface is intentionally small
- transport support is currently focused on a single API execution path
- organization-aware permission models are not implemented

## What “Done” Means For V1

V1 is complete when a real authenticated request can execute a real tool safely, return a predictable response, and leave an audit trail another engineer can understand.

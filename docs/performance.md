# Performance

## Goal

Track a basic baseline before real tool load and provider traffic increase.

## Current Baseline Areas

- app boot health
- health endpoint response
- queue dispatch path
- MCP execution pipeline duration
- audit logging overhead

## What Looks Good Right Now

- The execution flow is structured and testable.
- Queue preparation exists.
- Health endpoints are fast and simple.

## What Still Limits Real Performance Readiness

- local SQLite does not reflect production queue or dashboard performance
- no load testing yet
- no provider latency benchmarking yet
- no external APM yet

## Bottlenecks To Watch Later

- tool execution duration
- queue backlog under burst traffic
- Redis latency in production
- audit log volume

## Next Improvements

- measure tool duration by type
- benchmark Redis-backed queues
- track slow-tool thresholds
- compare local and production-like database behavior

# Rollback

## Goal

Rollback should be fast, predictable, and boring.

## First Rollback Tool

Use feature flags first.

If a specific MCP capability is causing trouble:

- disable the related Pennant feature
- stop sending traffic to the failing path
- verify health and queue stability

## Second Rollback Tool

Use deployment rollback if feature flags are not enough.

Typical flow:

1. disable the affected feature flag
2. roll back the deployment
3. restart or terminate Horizon so workers reload older code
4. verify `/health`
5. inspect queue failures, Horizon, Telescope, and Pulse

## Rollback Triggers

- MCP execution failures spike
- Redis failures break queue processing
- health endpoints return `503`
- queue latency becomes unsafe
- a release introduces widespread application errors

## After Rollback

- confirm health endpoints
- confirm queue processing
- confirm Horizon status
- confirm feature flag state
- capture root cause notes

## Current Limitation

Rollback is still mostly process and discipline, not full release automation.

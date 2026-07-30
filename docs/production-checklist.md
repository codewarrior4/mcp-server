# Production Checklist

## Infrastructure

- [ ] HTTPS enabled
- [ ] `APP_DEBUG=false`
- [ ] production secrets configured
- [ ] Redis available
- [ ] queue workers running
- [ ] Horizon running
- [ ] scheduler running
- [ ] database backups configured

## Security

- [ ] Sanctum token usage reviewed
- [ ] internal dashboards restricted by `INTERNAL_DASHBOARD_EMAILS`
- [ ] feature flags reviewed before release
- [ ] logs protected
- [ ] Telescope not left open in non-local environments

## Reliability

- [ ] health endpoints verified
- [ ] CI passing
- [ ] MCP tests passing
- [ ] queue pruning schedules active
- [ ] Telescope pruning active
- [ ] Sanctum token pruning active
- [ ] Horizon snapshot scheduling active

## Queues

- [ ] `QUEUE_CONNECTION=redis` in production if Horizon is used
- [ ] Redis connection verified
- [ ] retry and timeout values reviewed
- [ ] failed jobs monitoring in place
- [ ] batch pruning active

## Observability

- [ ] Pulse enabled in the target environment
- [ ] Telescope enabled only where appropriate
- [ ] MCP execution logs verified
- [ ] MCP failure events visible
- [ ] alerts configured

## Release Readiness

- [ ] migrations reviewed
- [ ] rollback path documented
- [ ] deployment steps documented
- [ ] Horizon restart step included in deployment flow

#!/bin/sh

# Create log directory
mkdir -p /var/log

# Sync immediately with timeout (don't block startup)
timeout 30 php /opt/sync/index.php >> /var/log/descroix-sync.log 2>&1 || true

# Start cron daemon
cron &

exec "$@"

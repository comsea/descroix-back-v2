#!/bin/sh
set -e

# Populate dataset.xml immediately so a fresh deploy isn't empty
# until the first cron tick (up to 15min later).
php /opt/sync/index.php || true

# Ensure log directory exists with proper permissions
mkdir -p /var/log
chmod 777 /var/log

# Load cron jobs
crontab /etc/cron.d/descroix-sync

# Start cron daemon
cron

# Monitor and restart cron if it crashes (common issue in containers)
(
    while true; do
        sleep 30
        if ! ps -e | grep -v grep | grep -q cron; then
            echo "[$(date)] Cron daemon stopped, restarting..." >&2
            crontab /etc/cron.d/descroix-sync
            cron
        fi
    done
) &

exec "$@"

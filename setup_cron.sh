#!/bin/bash

CRON_CMD="php $(pwd)/cron.php"
CRON_JOB="0 0 * * * $CRON_CMD"

( crontab -l 2>/dev/null | grep -v -F "$CRON_CMD" ; echo "$CRON_JOB" ) | crontab -
echo "Cron job set to run every 24 hours."

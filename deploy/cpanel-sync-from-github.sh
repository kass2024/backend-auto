#!/bin/bash
# Same as cpanel-git-pull.sh — git pull only, no clone.
exec "$(dirname "$0")/cpanel-git-pull.sh" "$@"

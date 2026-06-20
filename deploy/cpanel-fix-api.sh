#!/bin/bash
# Wrapper — runs self-contained fix at backend root.
exec "$(dirname "$0")/../cpanel-fix-api.sh" "$@"

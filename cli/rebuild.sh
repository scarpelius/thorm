#!/bin/bash
set -euo pipefail

WATCH_DIR="/home/transphorm/playground/php-js-primitives"
CMD=(php /home/transphorm/playground/php-js-primitives/examples/hronic/hronic.php)
COOLDOWN_MS=300

# Detect filesystem type (vboxsf => VirtualBox shared folder)
FS_TYPE=$(stat -f -c %T "$WATCH_DIR" 2>/dev/null || echo unknown)
USE_POLL=0
if [[ "$FS_TYPE" == "vboxsf" || "$FS_TYPE" == "fuseblk" || "$FS_TYPE" == "unknown" ]]; then
  USE_POLL=1
fi

echo "Watching $WATCH_DIR (fs: $FS_TYPE)"

debounce() {
  local last=0 now
  while read -r _; do
    now=$(($(date +%s%3N)))
    if (( now - last >= COOLDOWN_MS )); then
      last=$now
      "${CMD[@]}" || true
    fi
  done
}

if (( USE_POLL == 0 )) && command -v inotifywait >/dev/null 2>&1; then
  # Event-driven (works on native/ext4 inside guest)
  inotifywait -m -r \
    -e close_write,create,delete,move \
    --exclude '(^|/)\.git(/|$)|(^|/)(vendor|node_modules)(/|$)|(\.swp$|~$|\.tmp$)' \
    --format '%w%f|%e' "$WATCH_DIR" | debounce
else
  # Polling fallback (works on vboxsf/SMB/NFS)
  echo "inotify unavailable here → falling back to polling."
  last_hash=""
  while true; do
    # Hash path+size+mtime; ignore noisy dirs
    new_hash=$(find "$WATCH_DIR" -type f \
      -not -path "*/.git/*" -not -path "*/vendor/*" -not -path "*/node_modules/*" \
      -printf '%P %s %T@\n' | sort | sha1sum | awk '{print $1}')
    if [[ "$new_hash" != "$last_hash" ]]; then
      echo "Change detected → rebuilding…"
      "${CMD[@]}" || true
      last_hash="$new_hash"
    fi
    sleep 1
  done
fi

#!/usr/bin/env bash
set -euo pipefail

# --- COLORS ---
YELLOW=$'\033[33m'
RESET=$'\033[0m'

# --- PATHS ---
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
EXAMPLES_DIR="$ROOT_DIR/examples"
RUNTIME_SRC="$ROOT_DIR/assets/runtime"
RUNTIME_DST="$ROOT_DIR/public/runtime"

COOLDOWN_MS=300
POLL_INTERVAL=1

DEBUG="${WATCH_DEBUG:-0}"

now_ms(){ printf '%s' "$(date +%s%3N)"; }

run_changed_script() {
  local changed="$1"
  [[ -f "$changed" ]] || return 0
  case "$changed" in
    *.php)
      echo "[examples] php: $changed"
      # Stream output; color only lines that start with "PHP Fatal error:"
      # Keep the real php exit code via PIPESTATUS.
      php "$changed" 2>&1 | awk -v Y="$YELLOW" -v R="$RESET" '
        /^PHP Fatal error:/ || /^PHP Warning:/ { printf "%s%s%s\n", Y, $0, R; next }
        { print }
      '
      return ${PIPESTATUS[0]}
      ;;
    *.sh)   echo "[examples] bash: $changed"; bash "$changed" ;;
    *)      if [[ -x "$changed" ]]; then
              echo "[examples] exec: $changed"; "$changed"
            else
              echo "[examples] sh (fallback): $changed"; sh "$changed"
            fi ;;
  esac
}

sync_runtime() {
  mkdir -p "$RUNTIME_DST"
  if command -v rsync >/dev/null 2>&1; then
    rsync -a --delete "$RUNTIME_SRC/" "$RUNTIME_DST/"
  else
    rm -rf "$RUNTIME_DST" && mkdir -p "$RUNTIME_DST"
    cp -a "$RUNTIME_SRC/." "$RUNTIME_DST/"
  fi
}

dir_hash() {
  local dir="$1"
  find "$dir" -type f \
    -not -path "*/.git/*" -not -path "*/vendor/*" -not -path "*/node_modules/*" \
    -printf '%P %s %T@\n' | sort | sha1sum | awk '{print $1}'
}

for d in "$ROOT_DIR" "$EXAMPLES_DIR" "$RUNTIME_SRC"; do
  [[ -d "$d" ]] || { echo "ERROR: Directory not found: $d"; exit 1; }
done
mkdir -p "$RUNTIME_DST"

# --- Mode select (allow manual override) ---
MODE="inotify"
if [[ "${1:-}" == "--poll" || "${WATCH_MODE:-}" == "poll" ]]; then
  MODE="poll"
else
  fs_type_root=$(stat -f -c %T "$ROOT_DIR" 2>/dev/null || echo unknown)
  if [[ "$fs_type_root" =~ ^(vboxsf|fuseblk|smb|nfs|unknown)$ ]] || ! command -v inotifywait >/dev/null 2>&1; then
    MODE="poll"
  fi
fi
echo "Watching root: $ROOT_DIR (mode: $MODE)"

last_examples_ms=0
last_runtime_ms=0
last_root_ms=0

handle_examples_change() {
  local path="$1" t; t=$(now_ms)
  (( t - last_examples_ms < COOLDOWN_MS )) && return 0
  last_examples_ms=$t
  echo "[examples] change: $path"
  run_changed_script "$path" || echo "[examples] script failed ($?)"
}

handle_runtime_change() {
  local t; t=$(now_ms)
  (( t - last_runtime_ms < COOLDOWN_MS )) && return 0
  last_runtime_ms=$t
  echo "[runtime] change → syncing $RUNTIME_SRC → $RUNTIME_DST"
  sync_runtime || echo "[runtime] sync failed ($?)"
}

handle_root_change() {
  local path="$1" t; t=$(now_ms)
  if [[ "$path" == "$EXAMPLES_DIR"* || "$path" == "$RUNTIME_SRC"* ]]; then return 0; fi
  (( t - last_root_ms < COOLDOWN_MS )) && return 0
  last_root_ms=$t
  echo "[root] change: $path"
  # Hook a full rebuild here if wanted:
  # php "$ROOT_DIR/cli/full_rebuild.php"
}

run_inotify() {
  # Include MODIFY and ATTRIB for Vagrant/VirtualBox saves
  local EVENTS="modify,attrib,close_write,create,delete,move"
  while IFS='|' read -r path events; do
    (( DEBUG == 1 )) && echo "[debug] $events $path"
    if [[ "$path" == "$EXAMPLES_DIR"* ]]; then
      [[ -f "$path" || "$events" =~ DELETE|MOVED_FROM ]] && handle_examples_change "$path"
    elif [[ "$path" == "$RUNTIME_SRC"* ]]; then
      handle_runtime_change
    else
      handle_root_change "$path"
    fi
  done < <(
    inotifywait -m -r \
      -e "$EVENTS" \
      --exclude '(^|/)\.git(/|$)|(^|/)(vendor|node_modules)(/|$)|(\.swp$|~$|\.tmp$)' \
      --format '%w%f|%e' \
      "$ROOT_DIR"
  )
}

run_polling() {
  echo "Polling every ${POLL_INTERVAL}s…"
  last_hash_root=""
  last_hash_examples=""
  last_hash_runtime=""
  while true; do
    new_hash_root=$(dir_hash "$ROOT_DIR")
    if [[ "$new_hash_root" != "$last_hash_root" ]]; then
      changed_any=$(find "$ROOT_DIR" -type f \
        -not -path "*/.git/*" -not -path "*/vendor/*" -not -path "*/node_modules/*" \
        -printf '%T@ %p\n' | sort -nr | head -n1 | awk '{print $2}')
      if [[ -n "${changed_any:-}" ]]; then
        (( DEBUG == 1 )) && echo "[debug] poll changed: $changed_any"
        if [[ "$changed_any" == "$EXAMPLES_DIR"* ]]; then
          handle_examples_change "$changed_any"
        elif [[ "$changed_any" == "$RUNTIME_SRC"* ]]; then
          handle_runtime_change
        else
          handle_root_change "$changed_any"
        fi
      fi
      last_hash_root="$new_hash_root"
    fi
    # Extra precision for hot dirs
    new_hash_examples=$(dir_hash "$EXAMPLES_DIR")
    if [[ "$new_hash_examples" != "$last_hash_examples" ]]; then
      changed_file=$(find "$EXAMPLES_DIR" -type f -printf '%T@ %p\n' | sort -nr | head -n1 | awk '{print $2}')
      [[ -n "${changed_file:-}" ]] && handle_examples_change "$changed_file"
      last_hash_examples="$new_hash_examples"
      printf "\n"
    fi
    new_hash_runtime=$(dir_hash "$RUNTIME_SRC")
    if [[ "$new_hash_runtime" != "$last_hash_runtime" ]]; then
      handle_runtime_change
      last_hash_runtime="$new_hash_runtime"
      printf "\n"
    fi
    sleep "$POLL_INTERVAL"
  done
}

if [[ "$MODE" == "inotify" ]]; then
  run_inotify
else
  echo "Note: using polling (good for Vagrant/VirtualBox/SMB/NFS)."
  run_polling
fi

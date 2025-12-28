#!/usr/bin/env bash
set -euo pipefail

# --- COLORS ---
YELLOW=$'\033[33m'
RESET=$'\033[0m'

# --- PATHS ---
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUNTIME_SRC="$ROOT_DIR/assets/runtime"
RUNTIME_DST_DEFAULT="$ROOT_DIR/public/thorm/js"
RUNTIME_DST="${THORM_RUNTIME_DST:-$RUNTIME_DST_DEFAULT}"

COOLDOWN_MS=300
POLL_INTERVAL=1

DEBUG="${WATCH_DEBUG:-0}"

now_ms(){ printf '%s' "$(date +%s%3N)"; }

prompt_existing_dir() {
  local var_name="$1" prompt="$2" default="$3" input
  while true; do
    if [[ -n "$default" ]]; then
      read -r -p "$prompt [$default]: " input
      [[ -z "$input" ]] && input="$default"
    else
      read -r -p "$prompt: " input
    fi
    if [[ -n "$input" && -d "$input" ]]; then
      printf -v "$var_name" '%s' "$input"
      return 0
    fi
    echo "Directory not found: $input"
  done
}

prompt_output_dir() {
  local var_name="$1" prompt="$2" default="$3" input
  while true; do
    if [[ -n "$default" ]]; then
      read -r -p "$prompt [$default]: " input
      [[ -z "$input" ]] && input="$default"
    else
      read -r -p "$prompt: " input
    fi
    if [[ -n "$input" ]]; then
      mkdir -p "$input"
      printf -v "$var_name" '%s' "$input"
      return 0
    fi
    echo "Please provide a path."
  done
}

sync_runtime() {
  mkdir -p "$RUNTIME_DST"
  if command -v rsync >/dev/null 2>&1; then
    rsync -a "$RUNTIME_SRC/" "$RUNTIME_DST/"
  else
    cp -a "$RUNTIME_SRC/." "$RUNTIME_DST/"
  fi
}

init_runtime() {
  mkdir -p "$RUNTIME_DST"
  if [[ -z "$(find "$RUNTIME_DST" -type f -print -quit 2>/dev/null)" ]]; then
    echo "[runtime] initial copy: $RUNTIME_SRC -> $RUNTIME_DST"
    sync_runtime
  else
    echo "[runtime] destination has files; skipping initial copy"
  fi
}

run_changed_script() {
  local changed="$1"
  [[ -f "$changed" ]] || return 0
  case "$changed" in
    *.php)
      echo "[build] php: $changed"
      THORM_OUTPUT_DIR="$OUTPUT_DIR" \
      THORM_IR_DIR="$OUTPUT_DIR" \
      THORM_HTML_DIR="$OUTPUT_DIR" \
      php "$changed" 2>&1 | awk -v Y="$YELLOW" -v R="$RESET" '
        /^PHP Fatal error:/ || /^PHP Warning:/ { printf "%s%s%s\n", Y, $0, R; next }
        { print }
      '
      return ${PIPESTATUS[0]}
      ;;
    *)
      (( DEBUG == 1 )) && echo "[build] ignored: $changed"
      ;;
  esac
}

dir_hash() {
  local dir="$1"
  find "$dir" -type f \
    -not -path "*/.git/*" -not -path "*/vendor/*" -not -path "*/node_modules/*" \
    -printf '%P %s %T@\n' | sort | sha1sum | awk '{print $1}'
}

if [[ ! -d "$RUNTIME_SRC" ]]; then
  echo "ERROR: Directory not found: $RUNTIME_SRC"
  exit 1
fi

DEFAULT_BUILD_DIR=""
for d in "$ROOT_DIR/appers" "$ROOT_DIR/app" "$ROOT_DIR/examples"; do
  [[ -d "$d" ]] && DEFAULT_BUILD_DIR="$d" && break
done

DEFAULT_OUTPUT_DIR=""
for d in "$ROOT_DIR/writable/cache" "$ROOT_DIR/writable" "$ROOT_DIR/public"; do
  [[ -d "$d" ]] && DEFAULT_OUTPUT_DIR="$d" && break
done

BUILD_DIR="${THORM_BUILD_DIR:-}"
OUTPUT_DIR="${THORM_OUTPUT_DIR:-}"

if [[ -z "$BUILD_DIR" ]]; then
  prompt_existing_dir BUILD_DIR "Folder to watch for build scripts (php)" "$DEFAULT_BUILD_DIR"
fi

if [[ -z "$OUTPUT_DIR" ]]; then
  prompt_output_dir OUTPUT_DIR "Output dir for IR/HTML" "$DEFAULT_OUTPUT_DIR"
else
  mkdir -p "$OUTPUT_DIR"
fi

export THORM_OUTPUT_DIR="$OUTPUT_DIR"
export THORM_IR_DIR="$OUTPUT_DIR"
export THORM_HTML_DIR="$OUTPUT_DIR"

init_runtime

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

echo "Watching build dir: $BUILD_DIR (mode: $MODE)"
echo "Runtime assets: $RUNTIME_SRC -> $RUNTIME_DST"
echo "Output dir: $OUTPUT_DIR"

last_build_ms=0
last_runtime_ms=0

handle_build_change() {
  local path="$1" t; t=$(now_ms)
  (( t - last_build_ms < COOLDOWN_MS )) && return 0
  last_build_ms=$t
  echo "[build] change: $path"
  run_changed_script "$path" || echo "[build] script failed ($?)"
}

handle_runtime_change() {
  local t; t=$(now_ms)
  (( t - last_runtime_ms < COOLDOWN_MS )) && return 0
  last_runtime_ms=$t
  echo "[runtime] change: syncing $RUNTIME_SRC -> $RUNTIME_DST"
  sync_runtime || echo "[runtime] sync failed ($?)"
}

run_inotify() {
  # Include MODIFY and ATTRIB for Vagrant/VirtualBox saves
  local EVENTS="modify,attrib,close_write,create,delete,move"
  while IFS='|' read -r path events; do
    (( DEBUG == 1 )) && echo "[debug] $events $path"
    if [[ "$path" == "$BUILD_DIR"* ]]; then
      [[ -f "$path" || "$events" =~ DELETE|MOVED_FROM ]] && handle_build_change "$path"
    elif [[ "$path" == "$RUNTIME_SRC"* ]]; then
      handle_runtime_change
    fi
  done < <(
    inotifywait -m -r \
      -e "$EVENTS" \
      --exclude '(^|/)\.git(/|$)|(^|/)(vendor|node_modules)(/|$)|(\.swp$|~$|\.tmp$)' \
      --format '%w%f|%e' \
      "$BUILD_DIR" "$RUNTIME_SRC"
  )
}

run_polling() {
  echo "Polling every ${POLL_INTERVAL}s"
  last_hash_build=""
  last_hash_runtime=""
  while true; do
    new_hash_build=$(dir_hash "$BUILD_DIR")
    if [[ "$new_hash_build" != "$last_hash_build" ]]; then
      changed_file=$(find "$BUILD_DIR" -type f -printf '%T@ %p\n' | sort -nr | head -n1 | awk '{print $2}')
      [[ -n "${changed_file:-}" ]] && handle_build_change "$changed_file"
      last_hash_build="$new_hash_build"
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
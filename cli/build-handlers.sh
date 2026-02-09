#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="${THORM_ROOT:-$(cd "$SCRIPT_DIR/.." && pwd)}"
EXPR_DIR="$ROOT/src/runtime/core/handlers/expr"
if [[ ! -d "$EXPR_DIR" ]]; then
  if [[ -d "$PWD/src/runtime/core/handlers/expr" ]]; then
    ROOT="$PWD"
    EXPR_DIR="$ROOT/src/runtime/core/handlers/expr"
  else
    echo "Could not find handlers/expr under '$ROOT' or '$PWD'." >&2
    echo "Set THORM_ROOT to your repo root and re-run." >&2
    exit 1
  fi
fi
OP_DIR="$EXPR_DIR/op"
OUT="$ROOT/src/runtime/core/handlers/handlers.generated.js"
mkdir -p "$(dirname "$OUT")"

minify() {
  sed -E '1s/^\xEF\xBB\xBF//; s/^[[:space:]]+//; s/[[:space:]]+$//; /^[[:space:]]*$/d'
}

minify_after_header() {
  local header_lines=2
  awk -v h="$header_lines" '
    NR<=h { print; next }
    {
      sub(/^\xEF\xBB\xBF/,"");
      gsub(/^[[:space:]]+|[[:space:]]+$/, "");
      if ($0 != "") print
    }
  '
}

minify_keep_export_newlines() {
  awk '
    {
      sub(/^\xEF\xBB\xBF/,"");
      if ($0 ~ /^export[[:space:]]/) {
        if (out != "") { print out }
        out = $0
      } else {
        gsub(/^[[:space:]]+|[[:space:]]+$/, "");
        if ($0 != "") {
          if (out == "") out = $0;
          else out = out " " $0;
        }
      }
    }
    END { if (out != "") print out }
  '
}

expr_files=()
while IFS= read -r -d '' f; do
  expr_files+=("$f")
done < <(find "$EXPR_DIR" -maxdepth 1 -type f -name '*.js' -print0 | sort -z)

op_expr_file="$OP_DIR/op.js"
if [[ ! -f "$op_expr_file" ]]; then
  echo "Missing op handler: $op_expr_file" >&2
  exit 1
fi

op_files=()
while IFS= read -r -d '' f; do
  [[ "$f" == "$op_expr_file" ]] && continue
  op_files+=("$f")
done < <(find "$OP_DIR" -maxdepth 1 -type f -name '*.js' -print0 | sort -z)

{
  echo "// AUTO-GENERATED: do not edit."
  echo "// Source: $EXPR_DIR and $OP_DIR"
  for f in "${expr_files[@]}" "$op_expr_file" "${op_files[@]}"; do
    minify < "$f"
    echo
  done

  echo "export const opHandlers = {"
  for f in "${op_files[@]}"; do
    name=$(basename "$f" .js)
    echo "  $name,"
  done
  echo "};"

  echo "export const exprHandlers = {"
  for f in "${expr_files[@]}"; do
    name=$(basename "$f" .js)
    echo "  $name,"
  done
  echo "  op,"
  echo "};"
} | {
  read -r h1 || true
  read -r h2 || true
  echo "$h1"
  echo "$h2"
  minify_keep_export_newlines
} > "$OUT"

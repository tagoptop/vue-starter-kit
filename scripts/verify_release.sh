#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ARTISAN_PATH="$ROOT_DIR/artisan"
cd "$ROOT_DIR"

failures=0

if command -v php >/dev/null 2>&1; then
    PHP_CMD=(php)
elif command -v flatpak-spawn >/dev/null 2>&1 && flatpak-spawn --host command -v php >/dev/null 2>&1; then
    PHP_CMD=(flatpak-spawn --host php)
else
    PHP_CMD=()
fi

pass() {
    printf '[PASS] %s\n' "$1"
}

warn() {
    printf '[WARN] %s\n' "$1"
}

fail_check() {
    printf '[FAIL] %s\n' "$1"
    failures=$((failures + 1))
}

printf 'Release verification for %s\n' "$ROOT_DIR"

if [[ ${#PHP_CMD[@]} -gt 0 ]] && "${PHP_CMD[@]}" "$ARTISAN_PATH" --version >/dev/null 2>&1; then
    pass 'Artisan is available.'
else
    fail_check 'Artisan is not available. Check PHP and application bootstrapping.'
fi

migration_output_file="$(mktemp)"
if [[ ${#PHP_CMD[@]} -gt 0 ]] && "${PHP_CMD[@]}" "$ARTISAN_PATH" migrate:status --no-interaction >"$migration_output_file" 2>&1; then
    if grep -q 'Pending' "$migration_output_file"; then
        fail_check 'Pending migrations detected. Run php artisan migrate --force before go-live.'
        cat "$migration_output_file"
    else
        pass 'Migrations are reachable and no pending migrations were reported.'
    fi
else
    fail_check 'Could not read migration status. Check database connectivity and PHP database drivers.'
    cat "$migration_output_file"
fi
rm -f "$migration_output_file"

if [[ -L public/storage && -e public/storage ]]; then
    pass 'public/storage exists and is linked.'
else
    fail_check 'public/storage is missing or not a valid symlink. Run php artisan storage:link.'
fi

queue_connection="${QUEUE_CONNECTION:-}"
if [[ -z "$queue_connection" && -f .env ]]; then
    queue_connection="$(grep -E '^QUEUE_CONNECTION=' .env | tail -n 1 | cut -d '=' -f2- || true)"
fi
queue_connection="${queue_connection:-database}"

if [[ "$queue_connection" == "sync" || "$queue_connection" == "null" ]]; then
    warn "QUEUE_CONNECTION is '$queue_connection'; no background worker check required."
else
    if pgrep -af 'artisan queue:work|artisan horizon|artisan queue:listen' >/dev/null 2>&1; then
        pass "Queue worker detected for connection '$queue_connection'."
    else
        fail_check "No queue worker detected for connection '$queue_connection'. Start php artisan queue:work or Horizon before go-live."
    fi
fi

if [[ "$failures" -gt 0 ]]; then
    printf '\nRelease verification failed with %d issue(s).\n' "$failures"
    exit 1
fi

printf '\nRelease verification passed.\n'
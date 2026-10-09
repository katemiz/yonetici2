#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)"
ENV_FILE="$APP_ROOT/.env"

if [[ "$EUID" -ne 0 ]]; then
    printf 'Run this setup as the operating-system superuser (for example, with sudo).\n' >&2
    exit 1
fi

if [[ ! -f "$ENV_FILE" ]]; then
    printf 'Missing %s. Create the Laravel .env file before running setup.\n' "$ENV_FILE" >&2
    exit 1
fi

read -r -p 'Absolute private upload directory outside this project: ' MEDIA_PATH_INPUT
if [[ "$MEDIA_PATH_INPUT" != /* ]]; then
    printf 'The upload directory must be an absolute path.\n' >&2
    exit 1
fi

APP_ROOT_REAL="$(realpath -e -- "$APP_ROOT")"
MEDIA_PATH="$(realpath -m -- "$MEDIA_PATH_INPUT")"
case "$MEDIA_PATH/" in
    "$APP_ROOT_REAL/"*)
        printf 'The upload directory must be outside the application directory.\n' >&2
        exit 1
        ;;
esac
if [[ "$MEDIA_PATH" == '/' ]]; then
    printf 'The filesystem root cannot be used as an upload directory.\n' >&2
    exit 1
fi
if [[ "$MEDIA_PATH" == *'$'* ]]; then
    printf 'The upload directory cannot contain dollar signs because .env expands them.\n' >&2
    exit 1
fi

read -r -p 'PHP runtime user (must match the running web process): ' PHP_USER
if [[ -z "$PHP_USER" ]]; then
    printf 'The PHP runtime user is required. Use the account running FrankenPHP/PHP-FPM.\n' >&2
    exit 1
fi
if ! id "$PHP_USER" >/dev/null 2>&1; then
    printf 'System user %s does not exist.\n' "$PHP_USER" >&2
    exit 1
fi

DEFAULT_GROUP="$(id -gn "$PHP_USER")"
read -r -p "PHP runtime group [$DEFAULT_GROUP]: " PHP_GROUP
PHP_GROUP="${PHP_GROUP:-$DEFAULT_GROUP}"
if ! getent group "$PHP_GROUP" >/dev/null 2>&1; then
    printf 'System group %s does not exist.\n' "$PHP_GROUP" >&2
    exit 1
fi

if [[ -n "${PHP_BIN:-}" ]]; then
    PHP_CANDIDATES=("$PHP_BIN")
else
    PHP_CANDIDATES=(
        /usr/bin/php8.4
        /usr/bin/php8.3
        /usr/bin/php8.2
        /usr/bin/php8.1
        /usr/bin/php
        "$(command -v php || true)"
    )
fi

PHP_BIN=''
for candidate in "${PHP_CANDIDATES[@]}"; do
    if [[ -x "$candidate" ]] \
        && "$candidate" "$APP_ROOT/artisan" --version >/dev/null 2>&1 \
        && "$candidate" -r 'exit(($argv[1] ?? "") === "private-media-probe" ? 0 : 1);' private-media-probe >/dev/null 2>&1; then
        PHP_BIN="$candidate"
        break
    fi
done

if [[ -z "$PHP_BIN" ]]; then
    printf 'A working PHP CLI was not found; set PHP_BIN and rerun this script.\n' >&2
    exit 1
fi

install -d -o "$PHP_USER" -g "$PHP_GROUP" -m 0750 -- "$MEDIA_PATH"

FALLBACK_MEDIA_PATH="$(dirname -- "$APP_ROOT")/private-media"
if [[ "$FALLBACK_MEDIA_PATH" != "$MEDIA_PATH" && -d "$FALLBACK_MEDIA_PATH" ]]; then
    cp -a -n -- "$FALLBACK_MEDIA_PATH/." "$MEDIA_PATH/"
    chown -R -- "$PHP_USER:$PHP_GROUP" "$MEDIA_PATH"
    find "$MEDIA_PATH" -type d -exec chmod 0750 {} +
    find "$MEDIA_PATH" -type f -exec chmod 0640 {} +
fi

TEMP_ENV="$(mktemp "${ENV_FILE}.XXXXXX")"
trap 'rm -f -- "$TEMP_ENV"' EXIT
"$PHP_BIN" -r '
$envFile = $argv[1];
$path = $argv[2];
$contents = file_get_contents($envFile);

if ($contents === false) {
    fwrite(STDERR, "Could not read the environment file.\n");
    exit(1);
}

$escapedPath = str_replace(["\\", "\""], ["\\\\", "\\\""], $path);
$line = "MEDIA_UPLOADS_PATH=\"" . $escapedPath . "\"";
$lines = preg_split("/\\r\\n|\\n|\\r/", $contents);
$updatedLines = [];
$found = false;

foreach ($lines as $existingLine) {
    if (str_starts_with($existingLine, "MEDIA_UPLOADS_PATH=")) {
        if (! $found) {
            $updatedLines[] = $line;
            $found = true;
        }

        continue;
    }

    $updatedLines[] = $existingLine;
}

if (! $found) {
    $updatedLines[] = $line;
}

echo rtrim(implode(PHP_EOL, $updatedLines), "\\r\\n") . PHP_EOL;
' "$ENV_FILE" "$MEDIA_PATH" > "$TEMP_ENV"

chown --reference="$ENV_FILE" "$TEMP_ENV"
chmod --reference="$ENV_FILE" "$TEMP_ENV"
mv -- "$TEMP_ENV" "$ENV_FILE"
trap - EXIT

"$PHP_BIN" "$APP_ROOT/artisan" config:clear --no-interaction
printf 'Private uploads configured at %s (owner %s:%s, mode 0750).\n' "$MEDIA_PATH" "$PHP_USER" "$PHP_GROUP"
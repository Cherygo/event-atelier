#!/bin/sh
set -eu

project_root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
test_directory=$(mktemp -d)
test_project="event-atelier-check-$(date +%s)-$$"
test_image="event-atelier:$test_project"

compose() {
    docker compose --project-directory "$test_directory" \
        --env-file "$test_directory/.env.docker.production" \
        -f "$project_root/compose.production.yaml" -p "$test_project" "$@"
}

cleanup() {
    result=$?
    trap - EXIT
    if [ -f "$test_directory/.env.docker.production" ]; then
        if [ "$result" -ne 0 ]; then
            compose logs --tail=30 || true
        fi
        compose down --volumes --remove-orphans || true
    fi
    docker image rm "$test_image" >/dev/null 2>&1 || true
    rm -rf "$test_directory"
    exit "$result"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

docker build --target production -t "$test_image" "$project_root"

if docker run --rm "$test_image" php artisan about; then
    echo 'An image without APP_KEY must fail to start.' >&2
    exit 1
fi

umask 077
{
    printf 'APP_KEY='
    docker run --rm --entrypoint php "$test_image" -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
    printf 'DB_PASSWORD='
    docker run --rm --entrypoint php "$test_image" -r 'echo bin2hex(random_bytes(32)).PHP_EOL;'
    printf 'IMAGE_TAG=%s\n' "$test_project"
    printf 'APP_URL=http://localhost\nAPP_PORT=0\nSESSION_SECURE_COOKIE=false\nMAIL_MAILER=log\n'
} > "$test_directory/.env.docker.production"

compose up -d --no-build --wait --wait-timeout 180
for path in /up / /login /register /fonts/libre-caslon-display.ttf; do
    compose exec -T app curl --fail --silent "http://127.0.0.1:8080$path" >/dev/null
done

asset=$(compose exec -T app php -r 'echo json_decode(file_get_contents("public/build/manifest.json"), true, flags: JSON_THROW_ON_ERROR)["resources/js/app.js"]["file"];')
compose exec -T app curl --fail --silent "http://127.0.0.1:8080/build/$asset" >/dev/null
compose exec -T app sh -ec 'test ! -f .env; test ! -f public/hot; test ! -d vendor/phpunit; test ! -d node_modules; ! command -v node; ! command -v composer'
compose exec -T app sh -c 'printf persistent-upload > storage/app/public/docker-smoke.txt'
session_count=$(compose exec -T database psql -U event_atelier -d event_atelier -Atc 'SELECT count(*) FROM sessions')
test "$session_count" -gt 0

compose stop queue
queue_container=$(compose ps -aq queue)
test "$(docker inspect --format '{{.State.ExitCode}}' "$queue_container")" -eq 0
compose down
compose up -d --no-build --wait --wait-timeout 180

test "$(compose exec -T app curl --fail --silent http://127.0.0.1:8080/storage/docker-smoke.txt)" = persistent-upload
test "$(compose exec -T database psql -U event_atelier -d event_atelier -Atc 'SELECT count(*) FROM sessions')" -eq "$session_count"

echo 'Production Docker smoke checks passed.'

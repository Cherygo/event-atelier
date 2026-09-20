# Event Atelier

## Docker development

Requires Docker Engine with Docker Compose v2 or newer (Docker Desktop with WSL integration on Windows). PHP, Composer, and Node do not need to be installed on the host.

```sh
docker compose up -d --build --wait
docker compose logs -f app vite queue
```

Open localhost on port **8000**. The stack provides PHP 8.4 with Apache, Vite hot reload, PostgreSQL 17, a queue worker, and a scheduler. Setup installs the locked Composer/npm dependencies and runs migrations before services start. It uses `APP_KEY` from your local `.env` when available; otherwise, the development key is generated once and retained in the storage volume. No host `.env` is required.

Source files are mounted live; dependencies, PostgreSQL data, storage, and Laravel's bootstrap cache use separate Docker volumes. On Linux, set `LOCAL_UID` and `LOCAL_GID` to your user's IDs when building if they differ from 1000. `APP_PORT`, `VITE_PORT`, `VITE_HMR_HOST`, and `VITE_USE_POLLING` can be overridden in your shell or local `.env`. Enable polling if file changes are missed under WSL or Docker Desktop. Mail is written to container logs by default.

Run commands through the entrypoint so they receive the persisted development key:

```sh
docker compose exec app app-entrypoint php artisan migrate
docker compose exec app composer require vendor/package
docker compose exec app npm install package
docker compose restart queue
docker compose down
```

Run the existing test suite with its isolated in-memory database, overriding the development container's database settings:

```sh
docker compose exec -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: \
  -e CACHE_STORE=array -e QUEUE_CONNECTION=sync -e SESSION_DRIVER=array -e MAIL_MAILER=array \
  app app-entrypoint env APP_ENV=testing vendor/bin/phpunit
```

Restart the queue after changing job code. After pulling changed dependency lockfiles, run `docker compose up -d --build --wait` again. `docker compose down` preserves data; adding `--volumes` deletes the development database and other persisted state. Stop Vite before returning to host-based development so its `public/hot` file is removed.

### Host-based development with PostgreSQL

To run PHP and Vite on the host while using Docker for PostgreSQL:

```sh
docker compose up -d --wait database
# On a fresh checkout only: cp .env.example .env && composer run setup
php artisan migrate
composer run dev
```

The `.env.example` defaults connect to the development database at `127.0.0.1:5432`, using database/user `event_atelier` and password `local-development-password`. Keep `DB_PASSWORD` consistent with the database's initialized password. Set `FORWARD_DB_PORT` and the host's `DB_PORT` together if port 5432 is occupied. The Docker application connects over the internal network; both workflows share the same PostgreSQL data. The development database port is bound to loopback only. Existing SQLite files are not imported automatically.

## Testing invitation emails with Mailtrap Sandbox

Email testing uses [Mailtrap Email Sandbox](https://docs.mailtrap.io/email-sandbox/overview). Messages appear in your Mailtrap inbox, **not your real email inbox**. You do not need a verified sending domain or the Mailtrap Email Sending API.

1. Create a free [Mailtrap account](https://mailtrap.io/) and open your Email Sandbox inbox.
2. Open the inbox's SMTP integration settings and copy its host, port, username, and password.
3. Create a local `.env` from `.env.example` if you do not already have one. Set the following values, replacing the placeholders with your own Sandbox SMTP credentials:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_sandbox_username
MAIL_PASSWORD=your_sandbox_password
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="Event Atelier"
```

Use the host and port shown in your Mailtrap inbox if they differ. Leave `MAIL_URL` unset so it does not override these settings. Keep credentials in your local, Git-ignored `.env`; never commit them or share them in screenshots.

For host-based development, clear cached configuration and restart any running queue worker:

```sh
php artisan config:clear
php artisan queue:restart
```

### Docker mail configuration

The development Compose stack explicitly sets `MAIL_MAILER=log`, which takes precedence over `.env`. To enable Sandbox delivery, create a local `compose.override.yaml` with the following contents (no credentials belong in this file):

```yaml
services:
  app:
    environment:
      MAIL_MAILER: smtp
  queue:
    environment:
      MAIL_MAILER: smtp
  scheduler:
    environment:
      MAIL_MAILER: smtp
```

The other mail settings are read from your local `.env`, mounted with the source files. Apply the override and clear cached configuration:

```sh
docker compose up -d --wait
docker compose exec app app-entrypoint php artisan config:clear
docker compose restart queue scheduler
```

### Try an invitation

1. Register an Event Atelier account, create an event, and open **People & access**.
2. Invite a different email address that does not already have access to the event. An unregistered address works; inviting yourself as the owner is intentionally blocked.
3. Open the message in your Mailtrap Sandbox inbox and follow its invitation link. Use a private browser window or log out first, then register or sign in with the invited email address to accept.

Invitation links expire after seven days. For local testing, open them on the machine running the application; a localhost link is not accessible from someone else's device.

If the app reports success but no message arrives in your Sandbox, confirm the SMTP settings belong to that inbox and that `MAIL_MAILER` is not still `log`. The `MAILTRAP_*` variables and `php artisan mailtrap:send-test` command belong to a separate Email Sending API test; they are **not used by the invitation button**.

## Production Docker image

The default Dockerfile target builds a standalone image with production Composer dependencies and compiled frontend assets. It runs as `www-data`, serves only `public/` on port 8080, and checks `/up` for health. Node, Composer, host secrets, local databases, and development dependencies are excluded from the final image.

```sh
docker build --target production -t event-atelier:production .
cp .env.docker.production.example .env.docker.production
docker run --rm --entrypoint php event-atelier:production -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'
```

Put the generated key in `APP_KEY`, set `APP_URL` to the public HTTPS origin, and set a strong `DB_PASSWORD` in `.env.docker.production`. Keep that file private and retain the same key across deployments. Configure a real mail transport before using invitations or password resets.

```sh
docker compose --env-file .env.docker.production -f compose.production.yaml up -d --build --wait
docker compose --env-file .env.docker.production -f compose.production.yaml logs -f app queue
```

This is a single-host deployment: PostgreSQL and uploads live in named volumes. The database is not published to the host. Migrations must succeed before the web, queue, and scheduler containers start. Recreate all application services when deploying a new image so workers load the new code. Back up PostgreSQL and the storage volume before deployment; never use `down --volumes` on data you need to keep. Changing `DB_PASSWORD` after database initialization also requires changing the existing PostgreSQL role's password.

The app binds to loopback port 8080 by default. Terminate HTTPS with your hosting platform or reverse proxy and configure Laravel's trusted proxies for that environment. `SESSION_SECURE_COOKIE=true` assumes HTTPS; set it to `false` only when smoke-testing on local HTTP. `APP_BIND_ADDRESS` and `APP_PORT` control the published interface and port. This configuration does not provision a public server, TLS certificates, or backups.

For an external PostgreSQL or MySQL service, use the same image with your platform's environment variables and persistent upload storage; both PDO drivers are installed. The included Compose stacks intentionally use PostgreSQL.

Run `sh tests/docker-smoke.sh` to build and verify production startup, HTTP responses, compiled assets, required-key validation, and database/upload persistence across container recreation. It uses isolated containers and volumes and removes them afterward.

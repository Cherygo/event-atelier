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

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

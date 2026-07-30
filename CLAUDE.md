# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

This is the **Yii 2 Basic Project Template** (`yiisoft/yii2-app-basic`), a PHP 8.2+ MVC skeleton app. It currently contains only the stock template (login/logout, contact page) — no custom domain code has been added yet.

## Commands

Install dependencies:
```
composer install
```

Run the dev server (built-in PHP server):
```
php yii serve
```
or via Docker:
```
docker-compose up -d
```
App is then available at `http://127.0.0.1:8000` (Docker) or the `php yii serve` printed URL.

**Tests** (Codeception, suites: `unit`, `functional`, `acceptance`):
```
composer tests
# equivalent to:
vendor/bin/codecept run --env php-builtin
```
Run a single suite:
```
vendor/bin/codecept run Unit --env php-builtin
vendor/bin/codecept run Functional --env php-builtin
vendor/bin/codecept run Acceptance --env php-builtin
```
Run a single test file/method:
```
vendor/bin/codecept run tests/Unit/LoginTest.php --env php-builtin
vendor/bin/codecept run tests/Functional/ContactFormCest.php:testContactSubmit --env php-builtin
```
Under Docker, prefix with `docker compose exec -T php` and build actor classes first:
```
docker compose exec -T php vendor/bin/codecept build
docker compose exec -T php vendor/bin/codecept run
```
Acceptance tests default to the `PhpBrowser` module against `php-builtin`; switching to real-browser `WebDriver` testing requires editing `tests/Acceptance.suite.yml` and running a Selenium server (see README for details).

**Static analysis / linting:**
```
composer static   # phpstan --memory-limit=-1 (level 5, see phpstan.neon)
composer cs        # phpcs, ruleset phpcs.xml.dist (Yii2 coding standard)
composer cs-fix     # phpcbf, auto-fix coding standard violations
```

**Console commands** (via `yii` script, controllers live in `commands/`):
```
./yii <controller>/<action>
```

## Architecture

Standard Yii 2 MVC layout — most productivity comes from knowing which config file governs which context and how the framework wires things together, since individual classes are thin.

- **Entry points**: `web/index.php` (web app), `web/index-test.php` (test env web app), `yii` (console entry script).
- **Two application configs, merged from shared pieces**:
  - `config/web.php` — web app config (`id: basic`). Merges `config/params.php` + `config/db.php`. Registers `user.identityClass = app\models\User`, file-based cache/mailer, error handler routed to `site/error`, and (only when `YII_ENV_DEV`) bootstraps the `debug` and `gii` modules.
  - `config/console.php` — console app config (`id: basic-console`, namespace `app\commands`). Same DB/params, no web-only components.
  - `config/test.php` — test-environment overrides (uses `config/test_db.php`), loaded via `tests/_bootstrap.php` / `codeception.yml`.
  - `config/params.php` — shared, environment-independent app parameters (e.g. `adminEmail`).
- **Mailer**: configured as a singleton in `config/web.php` using `yii\symfonymailer\Mailer` with `useFileTransport = true` — outgoing mail is written to files under `runtime/mail`, not actually sent, until file transport is disabled.
- **Namespace `app\...`** maps to the project root (`@app` alias = `dirname(__DIR__)`), i.e. `app\models\User` → `models/User.php`, `app\controllers\SiteController` → `controllers/SiteController.php`, etc. There is no `src/` directory.
- **Auth**: `models/User.php` implements `yii\web\IdentityInterface` with a small hardcoded user array (demo/admin) — replace with real persistence before using authentication for anything beyond the template.
- **Assets**: `assets/AppAsset.php` defines the main CSS/JS bundle registered by `views/layouts/main.php`.
- **Views**: `views/layouts/main.php` is the shared layout (pulls in `_head.php`, `_header.php`, `_footer.php`); `views/site/*` are the controller-action views for `SiteController`; `mail/layouts/{html,text}.php` are the layouts used for outgoing email views.

## Testing structure

Codeception config lives at the repo root `codeception.yml`, with suite-specific YAML in `tests/*.suite.yml` and bootstrap files in `tests/_bootstrap.php` / `tests/<Suite>/_bootstrap.php`. Coverage (`composer tests` with `--coverage`) is scoped to `commands/`, `controllers/`, `mail/`, `models/`, `views/`, `widgets/`. `codeception/c3` (`c3.php` at repo root) provides code coverage collection for the acceptance/PhpBrowser suite — it is test tooling, not application code.

PHPStan (`phpstan.neon`) analyzes `assets`, `commands`, `controllers`, `mail`, `models`, `widgets`, and the three `tests/*` suites at level 5; its temp dir is `runtime/`.

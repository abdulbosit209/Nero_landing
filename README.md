# NERO — Mobile Auto Service Landing Page

A multi-language marketing/lead-generation landing page for **NERO**, a mobile auto
detailing/repair service in Tashkent (paintless dent removal, paint touch-up,
polishing/ceramic coating, glass restoration). Visitors browse services and pricing,
then submit a "get a quote" form that's relayed straight to a Telegram chat — there's
no database, no admin panel, no order pipeline.

Built on the [Yii 2 Basic Project Template](https://www.yiiframework.com/).

## Features

- **Landing page** composed of independent section partials (hero, services,
  advantages, recent work, pricing, comparison table, FAQ, contact/lead form).
- **Multi-language**: English, Russian and Uzbek, switchable at runtime and persisted
  in a cookie — no separate builds or routes per language.
- **Lead capture → Telegram**: form submissions (name, phone, service, description,
  optional photo) are validated server-side and posted to a Telegram bot chat via the
  Bot API. Nothing is persisted to a database.
- **Content is centrally defined**: which services/advantages/FAQ items appear, and in
  what order, lives in one PHP class (`models/LandingContent.php`) instead of being
  duplicated across view files.

## Tech stack

- PHP 8.2+, [Yii 2](https://www.yiiframework.com/) framework
- Bootstrap 5 (`yiisoft/yii2-bootstrap5`) for forms/layout primitives
- Symfony Mailer (`yiisoft/yii2-symfonymailer`) for the stock contact form
- Guzzle for the Telegram Bot API client
- [Codeception](https://codeception.com/) (Acceptance/Functional/Unit suites),
  PHPStan (level 5), PHPCS (Yii2 coding standard)

## Requirements

- PHP 8.2 or later, with the extensions Yii 2 normally needs (`mbstring`, `intl`,
  `fileinfo`, `gd` or `imagick`, etc. — see `requirements.php`)
- [Composer](https://getcomposer.org/)
- A web server (nginx/Apache + PHP-FPM, or `php yii serve` / Docker for local dev)

No database is required to run the site — `config/db.php` exists for Yii's
scaffolding but nothing in the app currently uses it.

## Getting started

```bash
composer install
```

Copy the local config template and fill in real secrets — this file is gitignored and
must never be committed:

```bash
cp config/params-local.php.example config/params-local.php
```

Edit `config/params-local.php`:

```php
return [
    // Generate with: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
    'cookieValidationKey' => 'a-long-random-string',
    'telegramBotToken' => '123456789:AA...',
    'telegramChatId' => '-1001234567890',
];
```

`cookieValidationKey` is required for CSRF/cookie protection to work — the app boots
with an empty one otherwise. See [docs/telegram-setup.md](docs/telegram-setup.md) for
how to obtain a Telegram bot token and chat id (the lead form silently logs a warning
to `runtime/logs/app.log` and no-ops if these aren't set).

Run the dev server:

```bash
php yii serve --router=router.php
```

(`router.php` makes the built-in server honor pretty URLs — see `config/web.php`'s
`urlManager`. Without it, any URL other than `/` 404s since the built-in server
doesn't read `web/.htaccess` the way Apache would.)

...or with Docker:

```bash
docker-compose up -d
```

The app is then available at `http://127.0.0.1:8000` (Docker) or the URL printed by
`php yii serve`.

## Configuration

Everything editorial/environment-specific lives in `config/params.php` (defaults) and
`config/params-local.php` (gitignored overrides — secrets and per-environment values):

| Key | Purpose |
|---|---|
| `cookieValidationKey` | Session/CSRF secret — see above |
| `telegramBotToken` / `telegramChatId` | Where lead-form submissions are sent |
| `brandName`, `phoneNumber`, `address` | Shown in the header, footer and contact section |
| `pricing` | Starting price per service slug, in UZS |
| `supportedLanguages` / `languageLabels` | Which languages exist and their switcher label |

### Adding/removing a landing page item

The set of services, "why choose us" advantages, recent-work entries, comparison rows
and FAQ items shown on the page is defined once, in `models/LandingContent.php`. To
add one:

1. Add its slug to the relevant constant in `LandingContent`.
2. Add the matching `"<section>.<slug>.*"` translation keys to **all three** files
   under `messages/{en,ru,uz}/app.php`.

No view file needs to change — every section partial (`views/site/_services.php`,
`_advantages.php`, `_works.php`, `_pricing.php`, `_comparison.php`, `_faq.php`) loops
over these constants.

### Adding a language

1. Add the language code to `supportedLanguages` and a label to `languageLabels` in
   `config/params.php`.
2. Create `messages/<code>/app.php` with the same keys as `messages/en/app.php`.

## Project structure

Standard Yii 2 MVC layout; the pieces specific to this project:

```
controllers/SiteController.php   Landing page, lead-form submission, stock contact/about pages
models/LandingContent.php        Single source of truth for which items each section shows
models/LeadForm.php              Validates lead-form submissions, relays to Telegram
models/ContactForm.php           Stock Yii2 template contact form (unlinked from the landing nav)
components/TelegramNotifier.php  Sends a lead (+ optional photo) to a Telegram chat via Bot API
components/LanguageSelector.php  Resolves the active language from ?lang= or a cookie
views/site/_*.php                One partial per landing page section, composed by views/site/index.php
messages/{en,ru,uz}/app.php      All translated copy, keyed by "<section>.<slug>.<field>"
config/params.php                Non-secret app parameters (see Configuration above)
config/params-local.php          Gitignored secrets/overrides (not committed)
docs/telegram-setup.md           Step-by-step Telegram bot setup
```

`views/site/contact.php` and `views/site/about.php` are unmodified leftovers from the
Yii 2 template — they're reachable (`/site/contact`, `/site/about`) but not linked from
the landing page nav, which uses `#contact` to jump to the lead form section instead.

## Testing & quality checks

```bash
composer tests    # Codeception: Acceptance, Functional and Unit suites
composer static   # PHPStan, level 5
composer cs        # PHPCS, Yii2 coding standard
composer cs-fix     # Auto-fix coding standard violations
```

Run a single suite or test:

```bash
vendor/bin/codecept run Functional --env php-builtin
vendor/bin/codecept run tests/Functional/LeadFormCest.php --env php-builtin
```

Under Docker, prefix commands with `docker compose exec -T php` and build actor
classes first (`vendor/bin/codecept build`).

## Deployment

The included `Dockerfile` builds a self-contained image (PHP built-in server, no
database required) that runs on any Docker-capable host. `render.yaml` is a
[Render](https://render.com) Blueprint that deploys straight from it on Render's free
tier:

1. Push this repo to GitHub (already done if you're reading this from there).
2. On Render: **New +** -> **Blueprint** -> connect this repo. Render reads
   `render.yaml` and provisions the service automatically.
3. Fill in the three secrets it prompts for (not stored in the blueprint):
   - `COOKIE_VALIDATION_KEY` — generate with
     `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`
   - `TELEGRAM_BOT_TOKEN` / `TELEGRAM_CHAT_ID` — see
     [docs/telegram-setup.md](docs/telegram-setup.md)
4. Deploy. You'll get a free `https://<service-name>.onrender.com` URL.

The free tier spins the service down after ~15 minutes of inactivity, so the first
request after a quiet period takes a few seconds to wake back up. `web/index.php`
reads `YII_ENV`/`YII_DEBUG` from the environment (see `render.yaml`), so debug mode
and dev-only modules (`debug`, `gii`) stay off in production automatically.

## License

BSD-3-Clause — see [LICENSE.md](LICENSE.md) (inherited from the Yii 2 Basic Project
Template this project is built on).

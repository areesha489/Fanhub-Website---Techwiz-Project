# FAN HUB+

**Explore. Discover. Connect.** — a premium dark-themed fandom platform for Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay.

FAN HUB+ is a full Laravel application: a public discovery surface (home, explore, category pages, catalogue indexes, detail pages and global search), a member area (dashboard, bookmarks with notes, ratings, notifications, preferences, fan submissions), a moderation-ready admin panel with analytics charts, a Sanctum-protected JSON API and an assistive chat widget that works offline out of the box.

Everything ships with original demo copy and generated SVG artwork — there is no copyrighted cover art and no external image CDN.

---

## Contents

- [Features](#features)
- [Tech stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Demo credentials](#demo-credentials)
- [Testing](#testing)
- [JSON API](#json-api)
- [Project structure](#project-structure)
- [Configuration](#configuration)
- [Limitations & honest notes](#limitations--honest-notes)

---

## Features

**Discovery (public)**
- Cinematic home page: hero slider, category tiles, trending titles, featured characters, latest articles, multimedia, upcoming events, releases and a merchandise showcase.
- Explore with **server-side** filters (free text, category, type, genre, year, sort, grid/list view) plus filter chips and pagination.
- Category pages (`/category/{slug}`) with counts, featured/trending rows and per-category filtering.
- Catalogue indexes for characters, articles, multimedia, events, merchandise and upcoming releases, each with search, filters and pagination.
- Global search (`/search?q=`) across seven content types with per-type result counts and tabs.
- Detail pages for titles, characters, articles, multimedia, events and merchandise (related items, galleries, view tracking).
- Generated placeholder artwork (`/ph/{label}.svg`) so every card always looks intentional.

**Accounts**
- Register / login / logout, forgot + reset password, signed email-verification links, suspended accounts blocked from signing in.
- Dashboard with personal stats, recent activity, bookmarks, submissions and notifications.
- Profile (name, bio, avatar upload, favourite fandoms) and settings (theme, font scale, reduced motion, notification toggles, password change).
- Notification centre (per-user, unread counts, mark-as-read).

**Engagement**
- **Bookmarks with personal notes** — AJAX toggle from any card, note editing with a per-bookmark note history, filter by type, search and sort on `/bookmarks`.
- **Ratings 1–10** from detail pages (one row per member per item, re-rating updates it).
- **Feedback form** (`/feedback`) — bug / suggestion / query tickets that land in the admin inbox.
- **Fan submissions** (`/submit-content`) — members queue artwork, videos, cosplay or write-ups for moderation; status tracked under `/my-submissions`.
- Activity log for logins, bookmark changes, ratings, feedback and submissions.

**Admin panel** (`/admin`, administrators only — everyone else gets 403)
- Dashboard and analytics with stat cards and **Chart.js** charts rendered from DB aggregates.
- CRUD for users, categories, content, characters, articles, media, events, merchandise and releases (search, sort, validation, image upload with GD downscale).
- Moderation queues for fan submissions and feedback (approve / reject / reply / delete).
- **Chatbot knowledge manager** — maintain the FAQ/keyword knowledge base plus a log of chatbot queries.
- Site settings (site name, announcement, chatbot toggle).

**Assistant**
- Floating chat widget on every page. With no API key it answers from the seeded knowledge base (42 FAQs across 10 topics, scored by keyword overlap + similarity); with `AI_API_KEY` set it replies through the configured provider and keeps the knowledge base as a safety net. Every exchange is stored in `chatbot_queries`.

**JSON API**
- Sanctum token auth and the same public/member/admin data as the website — see [JSON API](#json-api).

**Craft**
- Bootstrap 5.3 dark design system, fixed sidebar + glass topbar + offcanvas mobile navigation.
- Accessibility: skip link, labelled inputs, `aria-label` on icon buttons, `:focus-visible` outlines, reduced-motion and font-scale preferences.
- Loading, empty and error states on every data-driven page; server-rendered pagination.

---

## Tech stack

| Layer | Choice |
|---|---|
| Framework | **Laravel 13.33** (`laravel/framework` ^13.17) on **PHP 8.3** (8.3.33) |
| Auth | Session auth for the website, **Laravel Sanctum** personal access tokens for `/api` |
| Database | **MariaDB 13.0** (`fanhubplus`), tests use in-memory **SQLite** |
| UI | **Blade** templates + **Bootstrap 5.3.3** + vanilla JS (fetch/AJAX, no framework) |
| Icons | **Bootstrap Icons 1.11.3** (vendored) |
| Charts | **Chart.js 4.4.1** (vendored) |
| Assets | Static files in `public/assets/` — **no CDN, no build step required** |
| Mail | `MAIL_MAILER=log` (outbound mail is written to the log) |
| Tests | **PHPUnit 12** via `php artisan test` |

`package.json` / `vite.config.js` exist from the Laravel skeleton, but no rendered view uses `@vite` — the three layouts link `asset('assets/vendor/…')` and `asset('assets/css/app.css')` directly, so you do **not** need `npm install` or `npm run build` to run it. (The only `@vite` call lives in `resources/views/welcome.blade.php`, the unrouted skeleton home page.)

---

## Requirements

- PHP ≥ 8.3 with extensions: `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (image resizing).
- Composer 2.
- MariaDB 13 / MySQL 8 compatible server.
- (Optional) Node.js — only if you want to rebuild the unused Vite/Tailwind skeleton assets.

### Windows / this machine

| Thing | Where it is |
|---|---|
| PHP 8.3 | WinGet package `%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_*` |
| Composer | `%LOCALAPPDATA%\composer\composer.phar` (invoked as `php "$env:LOCALAPPDATA\composer\composer.phar" …`) |
| MariaDB 13.0 | `C:\Program Files\MariaDB 13.0\bin\mariadbd.exe`, listening on `127.0.0.1:3306` (started as a plain process — there is no Windows service) |
| Dev server | `http://127.0.0.1:8000` |

Every PowerShell session that runs `php`/`composer`/artisan needs the toolchain on `PATH` first:

```powershell
$phpDir = (Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Directory -Filter "PHP.PHP.8.3*").FullName
$env:PATH = "$phpDir;$env:LOCALAPPDATA\composer;$env:PATH"
```

---

## Installation

```powershell
# 1. Enter the project
cd "C:\Users\Sidd\Downloads\Fandom Universe"

# 2. (first run) put PHP + Composer on PATH — see the snippet above

# 3. Install PHP dependencies
php "$env:LOCALAPPDATA\composer\composer.phar" install

# 4. Create your environment file
Copy-Item .env.example .env
php artisan key:generate          # only if APP_KEY is empty

# 5. Point the DB credentials at your server (defaults match this machine)
#    DB_CONNECTION=mysql, DB_HOST=127.0.0.1, DB_PORT=3306,
#    DB_DATABASE=fanhubplus, DB_USERNAME=root, DB_PASSWORD=

# 6. Create the database, migrate and seed the demo catalogue
php artisan migrate --seed

# 7. Public uploads (avatars, admin images)
php artisan storage:link

# 8. Serve
php artisan serve --host=127.0.0.1 --port=8000
```

Then open <http://127.0.0.1:8000>.

### `start-dev.ps1`

The repo ships a launcher that does the boring parts for you:

```powershell
.\start-dev.ps1
```

It (1) puts PHP/Composer on `PATH`, (2) starts MariaDB on `:3306` if nothing is listening, (3) creates `.env` from `.env.example` when missing, (4) runs `php artisan migrate --force`, (5) creates `public/storage` when missing, and (6) serves the app on `http://127.0.0.1:8000`.

Other useful commands:

```powershell
php artisan migrate:status      # migration state
php artisan db:seed --force     # re-seed demo data (rows upsert by slug; factory extras may duplicate)
php artisan view:cache          # compile Blade templates
php artisan route:list          # every route
php artisan tinker              # REPL against the app
```

---

## Demo credentials

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@fanhubplus.test` | `FanHub!Admin2026` |
| Members (Priya, Marcus, Lena, Yuki, Sofia, …) | `priya@fanhubplus.test`, `marcus@fanhubplus.test`, … | `Password123!` |
| Unverified demo member | `noah@fanhubplus.test` | `Password123!` |

`moderator@fanhubplus.test` / `FanHub!Mod2026` is a regular (non-admin) account — useful for checking that `/admin` returns 403.

All demo users are created by `database/seeders/UserSeeder.php`.

---

## Testing

```powershell
php artisan test --compact
```

Details:

- Tests live in `tests/Feature` (HTTP/behaviour) and `tests/Unit`.
- `phpunit.xml` runs the suite against **in-memory SQLite** (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), so no MySQL server is needed and your real `fanhubplus` data is never touched.
- `CACHE_STORE`, `SESSION_DRIVER` and `MAIL_MAILER` are switched to `array` in tests.
- Feature tests use `RefreshDatabase` (migrate once per run, transaction per test) and either the deterministic seeders (`$this->seed()`, `$this->seed(ChatbotKnowledgeSeeder::class)`) or the model factories in `database/factories`.
- Individual test: `php artisan test --compact --filter=BookmarkTest`.
- Code style: `vendor/bin/pint <paths> --format agent` (this project is not a git repo, so avoid `pint --dirty`).

Covered areas: public pages and 404s, registration/login/logout and guest redirects, admin authorization (guest → 302, member → 403, admin → 200) plus a full admin CRUD cycle, bookmark AJAX/API contracts (create, idempotency, note update, delete, guest rejection), ratings, feedback, fan submissions and detail-page view tracking, the API envelope + pagination meta + guest 401 + token login, and the chatbot knowledge-base replies.

---

## JSON API

**Base URL:** `http://127.0.0.1:8000/api`

**Envelope**

```jsonc
// single resource
{ "data": { "id": 1, "title": "…" } }

// list (paginated)
{ "data": [ … ], "meta": { "current_page": 1, "last_page": 3, "per_page": 12, "total": 31, "from": 1, "to": 12 } }

// error
{ "message": "…", "errors": { "field": ["…"] } }   // 401 / 403 / 404 / 422 / 429
```

**Auth:** `POST /api/login` returns a Sanctum personal access token. Send it as `Authorization: Bearer {token}`.

### Examples

```bash
# List published titles (paginated)
curl -s "http://127.0.0.1:8000/api/content?per_page=2" \
  -H "Accept: application/json"

# Sign in and keep the token
TOKEN=$(curl -s -X POST "http://127.0.0.1:8000/api/login" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"admin@fanhubplus.test","password":"FanHub!Admin2026"}' | jq -r .token)

# Save an item with a note (201 + {"data": {...}})
curl -s -X POST "http://127.0.0.1:8000/api/bookmarks" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"type":"content","id":26,"note":"weekend watch"}'

# Personal dashboard (401 {"message":"Unauthenticated."} without a token)
curl -s "http://127.0.0.1:8000/api/dashboard" -H "Accept: application/json"
```

### Notable endpoints

| Area | Endpoints |
|---|---|
| Public reads | `GET /api/home`, `/api/categories`, `/api/categories/{slug}`, `/api/content`, `/api/content/{slug}`, `/api/search`, `/api/characters`, `/api/characters/{slug}`, `/api/articles`, `/api/articles/{slug}`, `/api/media`, `/api/media/{slug}`, `/api/events`, `/api/events/{slug}`, `/api/merchandise`, `/api/releases`, `/api/meta` |
| Auth | `POST /api/login`, `POST /api/register`, `POST /api/forgot-password`, `POST /api/reset-password`, `GET /api/user`, `POST /api/logout` |
| Member (`auth:sanctum`) | `GET /api/dashboard`, `GET|POST /api/bookmarks`, `PATCH|DELETE /api/bookmarks/{id}`, `POST /api/feedback`, `GET|POST /api/fan-submissions`, `POST /api/chat`, `GET|PUT /api/preferences`, `GET /api/notifications`, `PATCH /api/notifications/{id}` |
| Admin (`auth:sanctum` + `admin`) | `GET /api/admin/dashboard`, `GET /api/admin/analytics`, `GET|PUT /api/admin/settings`, CRUD for `users`, `categories`, `content`, `characters`, `articles`, `media`, `events`, `merchandise`, `releases`, `knowledge`, `GET|PATCH|DELETE /api/admin/submissions`, `GET|PATCH|DELETE /api/admin/feedback`, `GET /api/admin/chat-queries` |

Rate limits: `POST /api/login` and `/api/forgot-password` 5/min, `POST /api/register` and `POST /api/fan-submissions` 10/min, `POST /api/chat` 20/min (429 when exceeded).

---

## Project structure

```
app/
├── Http/
│   ├── Controllers/            # public pages: Home/Explore, Search, Category, Content,
│   │                           # Character, Article, Media, Event, Merchandise, Release,
│   │                           # Dashboard, Profile, Bookmark, Settings, Submission,
│   │                           # Notification, Rating, Feedback, Chat, Sitemap, Page
│   ├── Controllers/Admin/      # admin panel (dashboard, analytics, CRUD, moderation,
│   │                           # chatbot knowledge, settings)
│   ├── Controllers/Api/        # JSON API (+ Api/Admin/)
│   ├── Controllers/Auth/       # login, register, password reset, email verification
│   └── Middleware/             # EnsureUserIsAdmin ("admin" route alias)
├── Models/                     # 26 Eloquent models (slug route keys, casts, relations)
├── Services/ChatbotService.php # knowledge-base matching + optional AI provider
└── Support/                    # helpers.php (ph()/img()), ApiSerializer,
                                # BookmarkHelper, Engagement (views/log/notify)

database/
├── factories/                  # 14 factories (Faker copy via GeneratesFandomCopy)
├── migrations/                 # 30 migrations (status columns are strings, not MySQL ENUMs)
└── seeders/                    # DatabaseSeeder + Category, User, Content, Character,
                                # Article, Media, Event, Merchandise, Release, Tag,
                                # ChatbotKnowledge, Engagement, Feedback, FanSubmission,
                                # Setting seeders

public/
├── assets/css/app.css          # design system (all .fh-* classes)
├── assets/js/app.js            # vanilla JS: toasts, bookmarks, ratings, chat, charts
├── assets/vendor/              # bootstrap 5.3.3, bootstrap-icons 1.11.3, chart.js 4.4.1
└── images/catalogue/           # demo artwork referenced by the seeders

resources/views/
├── layouts/                    # app (public/user), guest (auth), admin
├── partials/                   # sidebar, topbar, mobile-nav, footer, chat-widget, flash
├── components/                 # x-hero, x-content-card, x-bookmark, x-filter-panel, …
│   └── admin/                  # x-admin.* table/form helpers
└── …                           # per-section pages (home, explore, characters, admin, …)

routes/                         # web.php, api.php, console.php
tests/Feature + tests/Unit      # PHPUnit feature suites
start-dev.ps1                   # Windows launcher (MariaDB + serve)
```

---

## Configuration

`.env.example` already contains everything the app reads. The values that matter:

| Variable | Default | Notes |
|---|---|---|
| `APP_URL` | `http://localhost:8000` | This machine uses `http://127.0.0.1:8000` — keep it in sync with `php artisan serve` so links and signed URLs are correct. |
| `DB_CONNECTION` / `DB_HOST` / `DB_PORT` | `mysql` / `127.0.0.1` / `3306` | MariaDB 13 on this machine. |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `fanhubplus` / `root` / *(empty)* | Create the DB before `php artisan migrate --seed`. |
| `SESSION_DRIVER` | `database` | Sessions, cache and queues all use DB tables — no Redis required. |
| `CACHE_STORE` | `database` | |
| `QUEUE_CONNECTION` | `database` | |
| `MAIL_MAILER` | `log` | Password-reset / verification mails are appended to `storage/logs/laravel.log`. |
| `LOG_CHANNEL` / `LOG_LEVEL` | `stack` / `debug` | |
| `AI_PROVIDER` | `openai` | `openai` \| `anthropic` \| `custom`. |
| `AI_API_KEY` | *(empty)* | **Optional.** Empty = the assistant runs fully offline on the seeded knowledge base. |
| `AI_MODEL` / `AI_BASE_URL` | `gpt-4o-mini` / `https://api.openai.com/v1` | |
| `AI_MAX_TOKENS` / `AI_TEMPERATURE` / `AI_TIMEOUT` | `512` / `0.7` / `20` | `AI_TIMEOUT` (seconds) is the HTTP timeout the provider call uses. |
| `CHATBOT_ENABLED` | `true` | Master switch for the AI path (KB answering still works). |

Email verification links and password resets are generated against `APP_URL`; with `MAIL_MAILER=log` you can copy them straight out of the log.

---

## Limitations & honest notes

- **Merchandise is a showcase, not a shop.** There is no cart, checkout, payment or stock tracking — items link out to their own product pages/stores by design.
- **The chatbot is knowledge-base-first.** Without `AI_API_KEY` it only answers from the seeded `chatbot_knowledge` rows (42 FAQs) and falls back to a canned offline reply; it is not a general-purpose LLM chat. Set a key and a provider to enable generated replies.
- **Single-server demo scope.** Database sessions/cache/queue, one MariaDB, no Horizon/Redis/Nginx/PWA/offline sync, no multi-tenant or multi-region story.
- **Generated artwork instead of real covers.** Every image falls back to `/ph/{label}.svg`; the seeders deliberately avoid copyrighted art (a few demo URLs in `public/images/catalogue/` are the exception).
- **Moderation is manual.** Submissions and feedback are reviewed in the admin panel; there are no webhooks or automated checks.
- **Role model is binary** (`user` / `admin`) with an `active` / `suspended` status — no granular permissions.
- **No automated browser tests.** The suite is PHPUnit feature/unit tests; nothing drives a real browser.

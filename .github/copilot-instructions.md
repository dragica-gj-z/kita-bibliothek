## Repo snapshot

- Framework: Laravel 12 (PHP 8.2+)
- Frontend: Vue 3 + Vite (resources/js, resources/css)
- Inertia present (inertiajs/inertia-laravel) but the app exposes both SPA and API routes
- Docker and local dev helpers included (Dockerfile, docker-compose.yml, laravel/sail in dev deps)

## Goal for an AI coding agent

Be immediately productive: implement small features, fix bugs, write tests, and update UI while respecting the repository's Laravel+Vue/Vite architecture and the project's existing conventions.

Keep changes small and local-first (composer/npm commands explained below). Prefer minimal, well-tested edits and add tests for behavioral changes.

## High-level architecture & where to look

- Backend: `app/` (models in `app/Models`, controllers in `app/Http/Controllers`, enums in `app/Enums`). Example models: `Book.php`, `Adult.php`, `Child.php`, `User.php`.
- Routes: `routes/web.php` — API-ish endpoints live here (e.g. `/api/show-books`, `/api/add-book`) while the SPA entrypoint is the `/{any}` wildcard returning `resources/views/welcome.blade.php`.
- Database: migrations in `database/migrations`. Data domain rules and enums live in `app/Enums` (e.g. `BookCondition.php`, `BookStatus.php`) and a project-specific `config/books.php` file exists for domain configuration.
- Frontend: `resources/js/` and `resources/css/` with Vue components under `resources/js/Components` and pages in `resources/js/Pages` (Vite + `laravel-vite-plugin` is used via `package.json`).

When investigating a feature, follow this path: route -> controller -> model -> migration/config -> frontend page/component.

## Developer workflows (exact, discoverable commands)

- Install PHP deps: `composer install`
- Install node deps: `npm install`
- Quick dev (local without Docker): `npm run dev` (Vite dev server) and `php artisan serve` — note there's a composer script `dev` that runs multiple processes via `npx concurrently` (it starts `php artisan serve`, `php artisan queue:listen`, `php artisan pail` and `npm run dev`). Use that only when you want to replicate the original dev combo.
- Build frontend for production: `npm run build`
- Run tests: `composer test` (runs `php artisan test` via composer scripts). PHPUnit is configured in `phpunit.xml` to use an in-memory sqlite DB for tests, so tests should be isolated and fast.
- Common artisan tasks:
  - `php artisan migrate` to run database migrations (or `php artisan migrate --graceful` used in composer scripts)
  - `php artisan key:generate` (composer scripts already generate key post-create)

- Docker: `docker-compose up -d` is available for containerized environments — the repo contains `Dockerfile` and `docker-compose.yml`.

## Project-specific conventions & patterns

- Tests expect SQLite in-memory by default (see `phpunit.xml`): avoid relying on MySQL-specific SQL in unit tests.
- API routes are registered in `routes/web.php` (not a separate `api.php`) and use controller methods like `AddBooksController::addBook` and `ShowBooksController::showBooks`.
- The front-end SPA is served from `resources/views/welcome.blade.php` (the wildcard route returns that view). Most UI changes go into `resources/js` and may require rerunning Vite (`npm run dev`) or rebuilding.
- Domain enums and small domain config live in `app/Enums` and `config/books.php` — prefer using these central enums/configs instead of duplicating strings in controllers or views.
- Background job/queue: composer `dev` script starts `php artisan queue:listen` — be mindful of queue behavior when making changes that dispatch jobs.

## Integration points & external dependencies

- Inertia: `inertiajs/inertia-laravel` is installed — parts of the app may use Inertia patterns. Confirm whether a page should be an Inertia response or a plain JSON API.
- Vite + `laravel-vite-plugin` tie frontend builds into blade templates. Changing asset imports may require updating `vite.config.js` or blade `@vite` tags (see `resources/views/welcome.blade.php`).
- Docker & Sail: `laravel/sail` is listed in dev deps. If reproducing CI-like environments, prefer using the provided docker config.

## Concrete examples to reference while editing

- Add-book flow (backend): `routes/web.php` -> `App\\Http\\Controllers\\AddBooksController::addBook` -> `app/Models/Book.php` -> `database/migrations/*_create_books_table.php` -> `config/books.php` (if needed).
- Show-books (API): `routes/web.php` -> `App\\Http\\Controllers\\ShowBooksController::showBooks` -> `resources/js/Pages` (frontend list component)
- Autocomplete/autofill endpoints: `KiFormController::autofill` and `KiFormController::confidenceOptions` (used by the children's UI)

## Editing guidance for AI agents (do/don't)

Do:
- Run unit/feature tests locally after code changes (`composer test`). PHPUnit runs are fast because of sqlite in-memory.
- Prefer making changes that are small, easily testable, and reversible. Add a targeted test when changing business logic.
- Respect existing enums in `app/Enums` and `config/books.php` for domain values.

Don't:
- Don't change DB connection assumptions in tests — tests rely on sqlite :memory: via `phpunit.xml`.
- Don't assume API endpoints live in `routes/api.php` — inspect `routes/web.php` first.

## Where to add docs/comments

- Add small READMEs or comments next to domain-specific code: `app/Enums`, `config/books.php`, and any non-obvious controller logic.

---
If any section above is unclear or you'd like the instructions expanded (for example: CI steps, common PR patterns, or specific component boundaries in `resources/js`), tell me which area to expand and I will iterate.

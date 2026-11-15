# CLAUDE.md

Guidance for AI coding assistants working in this repository.

## Project
Perigee Lounge, a website and management system for a fictional hookah lounge: public pages in en/lv/ru under a `{locale}` route prefix, a database-driven menu, reservations, QR table ordering, loyalty points and promo codes, with client, staff and admin roles. Laravel 13 (PHP 8.3), Blade, Tailwind CSS 4, Vite, Pest 4. SQLite locally, Docker setup with MySQL and Nginx.

## Commands
- `composer dev`: server, queue, scheduler, logs and Vite together
- `composer test`: test suite (`php artisan test --parallel --compact` for the full run)
- `composer stan`: Larastan, level 8, must stay at zero errors
- `vendor/bin/pint`: code style
- `npm run build`: production assets
- `php artisan migrate:fresh --seed`: rebuild the local database
- PHP needs the `gd` extension (QR codes).

## Architecture
- **Thin controllers:** validate with a Form Request, check access, call one action, return a response.
- **Actions** in `app/Actions` hold business operations, one class each. A blocked rule throws `BusinessRuleException` with a translation key.
- **Services** hold what isn't a single operation (`TableCart`, `TelegramNotifier`).
- **Authorization:** route groups use the `can:admin`, `can:staff` and `can:client` gates; per-record rules live in `OrderPolicy`, `ReservationPolicy` and `UserPolicy`. A client asking for someone else's record gets a 404.
- **Statuses** are backed enums cast on the models.
- **Translations** come from the database through `DatabaseTranslator`. New keys go in `DefaultTranslationsSeeder`; `php artisan translations:sync` adds them without overwriting edits.
- Design tokens are defined once in `resources/css/app.css` with Tailwind's `@theme`.

## Workflow
- One branch and one pull request per task, with several small commits.
- Commit and PR messages: short, plain sentence case, no `feat:`/`chore:` prefixes.
- Write tests only when behaviour is added or changed: a few focused ones that fail without the change.
- Run Pint, Larastan and the tests before opening a PR. CI runs the same checks.
- Browser tests (`tests/Browser`) run in CI on Ubuntu.
- Don't add dependencies without explaining why.

## Laravel rules
- **Validation:** Form Requests. Never pass `$request->all()` to `create()`, `update()` or `fill()`.
- **Mass assignment:** every model defines `$fillable`. Never `$guarded = []`.
- **Queries:** eager-load relationships with `with()` (strict mode turns lazy loading into an error outside production) and paginate lists. No raw SQL with interpolated values; use bindings.
- **Authorization:** every route that touches a record checks access through a gate or policy.
- **State changes** use POST, PUT, PATCH or DELETE with CSRF protection, never GET.
- **Comparisons:** strict `===` / `!==`; compare tokens and promo/QR secrets with `hash_equals()`. Passwords only through `Hash::make()` / `Hash::check()`.
- **Money and points:** order totals, loyalty points and promo-code usage change inside `DB::transaction()`, with `lockForUpdate()` or atomic updates where concurrent requests could race (for example two redemptions of the same code).
- **Uploads:** validate type and size, and store with a generated filename. Never keep the client's original filename.
- **Output:** escape with `{{ }}` in Blade. Use `{!! !!}` only for HTML the app generated itself or has sanitized. Escape guest input in Telegram messages.
- No `dd()`, `dump()`, `var_dump()`, `print_r()` or `ray()`, and no `env()` outside config files (Larastan enforces this).

## JavaScript rules
- `await` promises inside `try/catch`, and use `Promise.all` for independent requests.
- Use `let`/`const`, never `var`.
- Clear any interval, timeout or listener you start when it is no longer needed.

@.claude/local-notes.md

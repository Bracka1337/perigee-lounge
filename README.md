# Perigee Lounge

A website and management system for a fictional hookah lounge. Guests browse the menu and book tables, order from their table by QR code, and collect loyalty points. Staff run orders, reservations and promo codes, and admins manage users, tables and translations. The site is available in English, Latvian and Russian.

![Home page](docs/screenshots/home.png)

| Menu | Table ordering on a phone | Staff reservations |
|---|---|---|
| ![Menu](docs/screenshots/menu.png) | ![Table ordering](docs/screenshots/table-phone.png) | ![Staff reservations](docs/screenshots/staff-reservations.png) |

## Features

**Guests and clients**
- Public pages and a database-driven menu in three languages, under `/en`, `/lv` and `/ru`
- Table reservations on a floor plan, checked against opening hours and existing bookings
- QR table ordering with a cart
- Loyalty balance and promo codes with QR codes

**Staff**
- Orders, reservations and floor plan
- Menu management with drag-and-drop sorting and image uploads
- Promo codes with percent or fixed discounts, limited to a category or item
- QR scanner for loyalty and promo codes

**Admins**
- Dashboard with charts
- Users, tables and opening hours
- Translation editor and activity log

## Stack

Laravel 12, PHP 8.2, Blade, Tailwind CSS 4, Vite, Pest, Pint, Larastan (PHPStan level 7) and GitHub Actions. Uses SQLite locally, with a Docker setup for MySQL and Nginx.

## Getting started

You need PHP 8.2 or newer with the `gd` extension, Composer, and Node.js.

```bash
git clone https://github.com/Bracka1337/perigee-lounge.git
cd perigee-lounge
composer setup
php artisan db:seed
composer dev
```

Then open http://localhost:8000.

Demo accounts, for local use only:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@perigee.test` | `password` |
| Staff | `staff@perigee.test` | `password` |

Register a new account to try the client side. Email is written to the log by default, so the verification link is in `storage/logs/laravel.log`.

## How it works

- **Routing:** every page sits under a `{locale}` route prefix, and access is controlled by `auth` and `verified` middleware, `can:admin`/`can:staff`/`can:client` role gates, and policies for orders, reservations and users.
- **Translations:** interface text is stored in the database and served by a custom translator, so admins can edit it on the site. Defaults live in `DefaultTranslationsSeeder`, and `php artisan translations:sync` adds new keys without changing edited ones.
- **Design tokens:** colours and fonts are defined once in `resources/css/app.css` through Tailwind's `@theme`.
- **Telegram:** optional staff notifications for new orders and reservations, set up with the `TELEGRAM_*` values in `.env`.

## Tests

```bash
composer test          # Pest, in parallel
vendor/bin/pint --test
composer stan          # Larastan, level 7, no baseline
npm run build
```

GitHub Actions runs these on every pull request. Dependabot opens weekly grouped updates for Composer, npm and Actions.

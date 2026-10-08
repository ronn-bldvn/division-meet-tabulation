# Division Sports Meet — Tabulation System

Laravel + Tailwind (CDN) + MySQL. Three access levels:

- **Public** (`/`) — no login. Overall gold/silver/bronze medal tally, browse by sport, view each
  game's bracket (elimination → semis → final) and medal winners with result photos.
- **Sport Facilitator** (`/facilitator`) — logs in, scoped to one sport. Manages that sport's
  bracket matches (scores, winners, result photo uploads) and assigns gold/silver/bronze per game
  with a photo of the official result.
- **Super Admin** (`/admin`) — full control: sports, divisions/teams, games/events, and staff
  accounts (creates facilitator logins and assigns them to a sport).

## Files in this package

This is **not** a full Laravel install — only the application-specific files (this sandbox can't
reach Packagist/npm to run `composer install`). Drop these into a fresh Laravel skeleton:

```
app/Models/*.php                      → app/Models/
app/Http/Controllers/**/*.php         → app/Http/Controllers/
app/Http/Middleware/EnsureRole.php    → app/Http/Middleware/
database/migrations/*.php             → database/migrations/
database/seeders/DatabaseSeeder.php   → database/seeders/ (overwrite)
resources/views/**/*.blade.php        → resources/views/
routes/web.php                        → routes/ (overwrite)
```

## Setup

```bash
# 1. Create a fresh Laravel app (needs internet access to Packagist)
  composer create-project laravel/laravel sports-meet
cd sports-meet

# 2. Copy in every file from this package, overwriting routes/web.php and
#    database/seeders/DatabaseSeeder.php

# 3. Register the role middleware alias.
#    Laravel 11: in bootstrap/app.php, inside ->withMiddleware(function ($m) {...})
#    Laravel 10 or earlier: in app/Http/Kernel.php $middlewareAliases
```

`bootstrap/app.php` (Laravel 11) should include:

```php
->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureRole::class,
    ]);
})
```

```bash
# 4. Configure your database
cp .env.example .env      # or edit the .env this package includes
php artisan key:generate

# Create the MySQL database first, e.g.:
mysql -u root -p -e "CREATE DATABASE sports_meet_tabulation"

# 5. Migrate + seed sample data (sports, teams, a super admin, and one
#    facilitator account per sport — all with password: "password")
php artisan migrate --seed

# 6. Link storage so uploaded result photos are viewable
php artisan storage:link

# 7. Run it
php artisan serve
```

Visit `http://localhost:8000`:
- Public tally: the homepage, no login.
- Staff login: `/login`
  - Super Admin: `admin@meet.local` / `password`
  - Facilitator (e.g. Basketball): `basketball@meet.local` / `password`

**Change these passwords immediately** — update `database/seeders/DatabaseSeeder.php` before
seeding a real event, or update accounts afterward via `/admin/users`.

## How the pieces fit together

- **Medal tally** (`Team::tally()` in `app/Models/Team.php`) counts `medals` rows per team per
  type and sorts Olympic-style (gold desc, then silver, then bronze).
- **Brackets**: each `game_matches` row is one bracket slot (`round`: elimination / semifinal /
  final). A completed match's winner can auto-advance into `next_match_id`'s first open slot —
  set that field when creating matches to wire up bracket progression.
- **Medals**: one `medals` row per `(game, type)` — a facilitator picks the team and can attach a
  result photo as proof; this immediately reflects in the public tally.
- **Photo uploads** go to `storage/app/public/results/...` and are served via the `storage:link`
  symlink, shown as `<img>` on both public and facilitator pages.

## Extending

- Multiple facilitators per sport: currently one `sport_id` per user; add a pivot table if you
  need several facilitators per sport.
- Round robin scoring / standings table: the schema already supports it via `bracket_type`, you'd
  add a `round_robin` results view (points table) alongside the existing elimination bracket view.
- Real-time tally updates: pair with Laravel Echo/Pusher or simple polling (`wire:poll` if you add
  Livewire) if you want the public tally page to auto-refresh during the meet.

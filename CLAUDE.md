# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PowerChi 是 Laravel 11 + Bootstrap 5 + CoreUI 通用後台管理系統，附帶 MH Studio 公開前台網站。PHP 8.2+、MySQL 8.0、Node.js 18+。

## Common Commands

```bash
# Development
php artisan serve                    # Backend server
npm run dev                          # Vite watch mode
npm run build                        # Production build

# Testing & Quality
php artisan test                     # Pest tests
php artisan test --filter=TestName   # Single test
./vendor/bin/pint                    # PHP code formatting (PSR-12)
./vendor/bin/phpstan analyse         # Static analysis (Level 6)

# Database
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed     # Reset & seed
php artisan db:seed                  # Seed only

# Cache
php artisan optimize:clear           # Clear all caches
php artisan view:clear               # Clear compiled Blade views

# Scheduled Tasks
php artisan schedule:list            # View registered scheduled tasks
php artisan invoices:mark-overdue    # Manually mark overdue invoices
```

Default credentials: `admin@example.com` / `password`

## Architecture

### Two Separate Frontends

**Admin Backend** (`/admin/*`): CoreUI + Bootstrap 5 + Blade, routes in `routes/admin.php`, views in `resources/views/admin/`, layout `resources/views/layouts/admin.blade.php`. Assets: `resources/css/app.css` + `resources/js/app.js`.

**Public Frontend** (`/`): Custom dark-themed MH Studio site, routes in `routes/web.php`, views in `resources/views/frontend/`, layout `resources/views/frontend/layouts/app.blade.php`. Assets: `resources/css/frontend/mh-studio.scss` + `resources/js/frontend/mh-studio.js`.

Both asset bundles are registered in `vite.config.js` as separate entry points — they do not share CSS/JS.

### Code Organization Pattern

- **Controllers** → thin, delegate to Services/Actions
- **Services** (`app/Services/`): Business logic — Analytics, Cache, Dashboard, Seo
- **Actions** (`app/Actions/`): Single-purpose classes grouped by domain (Analytics, Article, Auth, Seo, User)
- **Helpers** (`app/Helpers/`): Global functions auto-loaded via `composer.json` — `helpers.php` (general) + `seo_helpers.php` (SEO)
- **Models**: Use Spatie traits (HasRoles, InteractsWithMedia, LogsActivity) + soft deletes on Article

### Key Packages (Spatie Ecosystem)

- `spatie/laravel-permission` — RBAC with roles: super-admin, admin, editor, author
- `spatie/laravel-activitylog` — Tracks model changes automatically
- `spatie/laravel-medialibrary` — File/image uploads with conversions
- `spatie/laravel-sitemap` — XML sitemap generation
- `spatie/laravel-analytics` — GA4 integration
- `barryvdh/laravel-dompdf` — PDF export for quotes and contracts

### Route Structure

- `routes/web.php` — Frontend pages + auth + contact/quote/newsletter POST + public JSON API (`/api/pricing`) + deploy helper routes
- `routes/admin.php` — All admin CRUD resources (middleware: auth, verified)
- `routes/console.php` — Scheduled tasks (daily invoice overdue marking at 00:30)
- Admin route prefix configurable via `ADMIN_PREFIX` env var (default: `admin`)
- Client portal routes: `/client/*` (auth middleware)

### Middleware Stack (registered in `bootstrap/app.php`)

Custom middleware appended to the `web` group in order:
1. **SecurityHeaders**: Adds X-Frame-Options, CSP, HSTS, X-XSS-Protection, Referrer-Policy headers to all responses
2. **SetLocale**: Resolves locale from query → session → cookie → config (supports zh_TW, en)
3. **TrackPageView**: Records page views to `analytics_events` table (skips admin/API/static/bots, anonymizes IP)

Public form routes also use `throttle` middleware: contact (5/min), quote-request (3/min), subscribe (5/min).

### Business Domain Models

Auto-numbered documents use a shared pattern: `{PREFIX}-YYYYMM-{SEQ}` (e.g., `INV-202603-001`, `QUO-202603-001`, `CTR-202603-001`, `QR-202603-001`). The number is auto-generated in each model's `boot()` method.

Key model groups:
- **CRM**: Client (status: lead/active/inactive/archived, tier: vip/premium/standard, source tracking) → hasMany Quotes, Invoices, Contracts, ClientInteractions, QuoteRequests
- **Financial**: Quote → QuoteItems, Invoice → InvoiceItems (both with `recalculate()` method), Contract → ContractItems. Quotes can convert to Invoice or Contract. Invoices support partial payments via `recordPayment()`.
- **Project Management**: Project (morphOne SeoMeta, many-to-many Clients) → Tasks, TimeEntries, Milestones, Files, Comments. Tasks use Kanban board with status workflow: todo → in_progress → in_review → completed.
- **Time Tracking**: TimeEntry (started_at, ended_at, duration_minutes) with `running()` scope for active timers, `billable()` scope, auto-calculates duration in `boot()`.
- **Pricing**: PricingCategory → PricingFeature (supports universal features via nullable `pricing_category_id`). Config in `config/quote-pricing.php` for timeline multipliers/labels
- **QuoteRequest**: Public-facing quote submissions with 64-char token for status tracking, `convertToQuote()` method to create formal Quote + QuoteItems. Auto-creates Client record if email is new.
- **Content**: Article (soft deletes), Category, Tag, Service → ServiceItems, Project, Testimonial
- **Communication**: ContactMessage, Subscriber, Newsletter → NewsletterLog. Newsletter sending uses chunked processing (200/batch) via `SendNewsletterJob`.
- **Templates**: ContractTemplate with type (service/maintenance/retainer/nda/other) for quick contract creation.

### Reorder / Sortable Pattern

13 models have an `order` column. Models should implement `scopeOrdered()` for consistent sorting. Admin controllers for Strategy A (small dataset) pages support drag-and-drop reorder via:
- Controller: `reorder()` method accepting `ids[]` array, updating order sequentially
- Route: `POST {resource}/reorder` (placed before `Route::resource()`)
- Controller: `_sortable` query parameter returns JSON `[{id, title, order}]` for the sortable UI
- View: `@include('admin.partials.sortable-mode', [...])` partial with SortableJS overlay

Pages with reorder: Services, Categories, Testimonials, ContractTemplates, PricingCategories, PricingFeatures.

Pages without reorder (Strategy B — time-sorted): Articles, Projects, Tasks, Quotes, Invoices, Contracts.

### Deploy Routes

Token-protected routes at `/deploy/*` for remote deployment (requires `DEPLOY_TOKEN` in `.env`):
- `/deploy/migrate?token=xxx` — `migrate --force`
- `/deploy/seed?token=xxx` — `db:seed --force`
- `/deploy/migrate-seed?token=xxx` — Both in sequence
- `/deploy/optimize?token=xxx` — `optimize:clear` + `optimize`

### Global Helpers

Defined in `app/Helpers/helpers.php` (auto-loaded via composer):
- `setting($key, $default)` — Get/set system config from DB (`settings` table)
- `active_route($routes, $activeClass)` — Route-based active class detection using `request()->routeIs()` with wildcard matching. Used in sidebar and nav links.
- `format_date()`, `format_file_size()`, `can_any()`, `flash_success/error/warning/info()`, `generate_slug()`, `truncate_html()`

SEO helpers in `app/Helpers/seo_helpers.php`: `seo_title()`, `seo_description()`, `set_seo_meta()`, `generate_schema_article()`, `generate_breadcrumb_schema()`, etc.

### Database Seeders

`DatabaseSeeder` runs in order: `RolePermissionSeeder` → `AdminUserSeeder` → `SettingSeeder` → `CategorySeeder` → `PricingSeeder` → `ContractTemplateSeeder` → `ServiceSeeder`

### Frontend SCSS Structure

```
resources/css/frontend/
├── mh-studio.scss     ← Entry point (@use all partials)
├── _variables.scss    ← CSS custom properties
├── _base.scss         ← Reset, animations, shared section styles
├── _layout.scss       ← Nav, hero, stats, footer
└── _sections.scss     ← Services, portfolio, process, tech, contact, quote-status, responsive
```

### Caching Strategy

- **Dashboard**: Stats cached 5 min, daily views + top pages cached 10 min, business KPI cached 5 min
- **Homepage**: All frontend homepage data cached 10 min (`homepage_data` key)
- **Pricing API**: Public pricing data cached 1 hour (`pricing_data` key)
- Cache is file-based by default (`CACHE_STORE=file`). Clear with `php artisan optimize:clear` or via admin settings UI.

### Config Files Worth Knowing

- `config/admin.php` — Pagination, upload limits, dashboard settings. **NOTE**: The `menu` array in this file is NOT used — see Gotchas below.
- `config/app.php` — Includes custom `deploy_token` key for deploy route protection.
- `config/quote-pricing.php` — Timeline multipliers, timeline/budget labels, admin notification email
- `config/seo.php` — Sitemap, robots.txt, Schema.org, OG/Twitter defaults
- `config/analytics.php` — GA4 property settings

## Gotchas & Patterns

### Admin Sidebar is Hardcoded

The admin sidebar is rendered by `resources/views/layouts/partials/sidebar.blade.php` — it is **fully hardcoded HTML** with Blade directives (`@can`, `@php` for badge counts). It does **NOT** read from `config/admin.php`'s `menu` array. To add/remove/reorder sidebar items, edit the Blade partial directly.

### Blade @json with Closures

Never pass PHP closures or complex expressions directly into `@json()` — the Blade compiler cannot parse array brackets `[]` inside closures within `@json()`, causing `ParseError: Unclosed '['`. Instead, prepare data in a `@php` block first, then pass the simple variable to `@json()`:

```blade
{{-- WRONG — causes ParseError --}}
<script>
  window.data = @json($items->map(function($item) {
      return ['id' => $item->id, 'name' => $item->name];
  }));
</script>

{{-- CORRECT — prepare in @php block first --}}
@php
    $dataForJs = $items->map(function($item) {
        return ['id' => $item->id, 'name' => $item->name];
    });
@endphp
<script>
  window.data = @json($dataForJs);
</script>
```

### Email/Mailable Pattern

Mailable classes follow the `ShouldQueue` pattern (see `app/Mail/QuoteRequestNotification.php`). Email templates live in `resources/views/emails/` and use inline CSS for compatibility.

### Admin View Conventions

- Admin views extend `layouts.admin` and set `@section('title', '...')`
- Breadcrumbs are set via `$breadcrumbs` array in `@php` block at top of view
- Tables use CoreUI's `.table-hover` with `.table-responsive` wrapper
- Status badges use `bg-{status_color}` with model accessors (e.g., `$invoice->status_color`)
- Delete buttons use `data-confirm-delete` attribute for JS confirmation
- Tooltips use `data-coreui-toggle="tooltip"`
- List/Grid toggle via `@include('admin.partials.view-toggle', ['pageKey' => '...'])`
- Drag-and-drop sort via `@include('admin.partials.sortable-mode', [...])`
- Icons use CoreUI Free: `<svg class="icon"><use xlink:href="/assets/icons/free.svg#cil-*"></use></svg>`
- Page-specific CSS/JS via `@push('styles')` and `@push('scripts')` stacks

### Login Rate Limiting

`LoginController` implements manual rate limiting (5 attempts/minute per login+IP). Uses `RateLimiter` facade with `Str::transliterate()` for consistent throttle keys.

### Auto-Order on Create

Strategy A controllers (Service, Category, Testimonial, Project, Task, ContractTemplate) auto-assign `order = max('order') + 1` in `store()` when user doesn't specify a value.

### Production Environment Checklist

The `.env` must be adjusted for production:
- `APP_DEBUG=false` (currently `true` for local dev)
- `TELESCOPE_ENABLED=false`
- `DEPLOY_TOKEN` set to a secure random string
- Strong `DB_PASSWORD`
- `SESSION_ENCRYPT=true` (already set)
- `APP_TIMEZONE=Asia/Taipei` (already set)

## Detailed Docs

See `docs/ARCHITECTURE.md`, `docs/SEO.md`, `docs/ROADMAP.md` for deeper technical documentation.

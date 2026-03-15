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
php artisan db:seed                  # Seed only (safe to re-run)

# Cache
php artisan optimize:clear           # Clear all caches
php artisan view:clear               # Clear compiled Blade views

# Validation after CSS/Blade changes
npm run build && php artisan view:cache   # Verify SCSS compiles + Blade syntax
php artisan view:clear                     # Clear after verification

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

### Route Loading

Admin routes are loaded **only** via `bootstrap/app.php`'s `then` callback. Do NOT add `require __DIR__.'/admin.php'` in `routes/web.php` — this was a previous bug that caused duplicate route registration.

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

- `routes/web.php` — Frontend pages + auth + contact/quote/newsletter POST + public JSON API (`/api/pricing`) + deploy helper routes + storage file fallback route
- `routes/admin.php` — All admin CRUD resources (middleware: auth, verified). Loaded via `bootstrap/app.php` only.
- `routes/console.php` — Scheduled tasks (daily invoice overdue marking at 00:30)
- Admin route prefix configurable via `ADMIN_PREFIX` env var (default: `admin`)
- Client portal routes: `/client/*` (auth middleware)

### Middleware Stack (registered in `bootstrap/app.php`)

Custom middleware appended to the `web` group in order:
1. **SecurityHeaders**: Adds X-Frame-Options, CSP (with YouTube/Instagram/LINE whitelist), HSTS, X-XSS-Protection, Referrer-Policy headers to all responses
2. **SetLocale**: Resolves locale from query → session → cookie → config (supports zh_TW, en)
3. **TrackPageView**: Records page views to `analytics_events` table (skips admin/API/static/bots, anonymizes IP)

Public form routes also use `throttle` middleware: contact (5/min), quote-request (3/min), subscribe (5/min).

### Business Domain Models

Auto-numbered documents use a shared pattern: `{PREFIX}-YYYYMM-{SEQ}` (e.g., `INV-202603-001`, `QUO-202603-001`, `CTR-202603-001`, `QR-202603-001`). The number is auto-generated in each model's `boot()` method.

Key model groups:
- **CRM**: Client (status: lead/active/inactive/archived, tier: vip/premium/standard, source tracking) → hasMany Quotes, Invoices, Contracts, ClientInteractions, QuoteRequests
- **Financial**: Quote → QuoteItems, Invoice → InvoiceItems (both with `recalculate()` method using `max(0, ...)` to prevent negative totals), Contract → ContractItems. Quotes can convert to Invoice or Contract. Invoices support partial payments via `recordPayment()`.
- **Project Management**: Project (morphOne SeoMeta, many-to-many Clients) → Tasks, TimeEntries, Milestones, Files, Comments. Tasks use Kanban board with status workflow: todo → in_progress → in_review → completed.
- **Time Tracking**: TimeEntry (started_at, ended_at, duration_minutes) with `running()` scope for active timers, `billable()` scope, auto-calculates duration in `boot()`.
- **Pricing**: PricingCategory → PricingFeature (supports universal features via nullable `pricing_category_id`). Config in `config/quote-pricing.php` for timeline multipliers/labels
- **QuoteRequest**: Public-facing quote submissions with 64-char token for status tracking, `convertToQuote()` method to create formal Quote + QuoteItems. Auto-creates Client record if email is new.
- **Content**: Article (soft deletes), Category, Tag, Service → ServiceItems, Project, Testimonial
- **Communication**: ContactMessage, Subscriber, Newsletter → NewsletterLog. Newsletter sending uses chunked processing (200/batch) via `SendNewsletterJob`.
- **Templates**: ContractTemplate with type (service/maintenance/retainer/nda/other) for quick contract creation.
- **Media**: MediaItem — custom media management (NOT Spatie MediaLibrary for general uploads). Files stored in `storage/app/public/uploads/YYYY/MM/` with UUID filenames. URL generated via relative path `/storage/{path}` (not `Storage::url()`) to avoid APP_URL mismatch issues.

### Settings System

`Setting` model provides a key-value store with DB-backed caching. Access via `setting($key, $default)` helper.

- Cache stores both `value` and `type` together (avoids N+1 DB queries per call)
- Cache is invalidated on model save/delete events
- Settings are grouped: `general`, `seo`, `analytics`, `mail`, `frontend`, `company`, `upload`
- **Frontend section toggles** (all default `'1'`): `section_stats_enabled`, `section_services_enabled`, `section_portfolio_enabled`, `section_process_enabled`, `section_techstack_enabled`, `newsletter_enabled`, `social_embed_enabled`
- **LINE integration**: `social_line`, `social_line_enabled`, `line_id`, `line_qrcode_url`
- **Social platforms**: 7 platforms each with URL + `_enabled` toggle (GitHub, LinkedIn, LINE, Facebook, Twitter, Instagram, YouTube)

### Media Upload Pattern

The project uses a **custom MediaItem model** (not Spatie MediaLibrary) for general file uploads:
- Upload: `MediaController::store()` → stores to `uploads/YYYY/MM/{UUID}.ext` on `public` disk
- URL: `MediaItem::url` returns relative path `/storage/{path}` (works without symlink via fallback route)
- **Media Picker**: Reusable modal at `@include('admin.media.partials.picker-modal')` with `openMediaPicker('inputId')` JS function. Used in settings for logo, favicon, LINE QR code, etc.
- **Storage fallback**: `GET /storage/{path}` route serves files from `storage/app/public/` when the `public/storage` symlink doesn't exist (common on Hostinger shared hosting)

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
- `/deploy/init?token=xxx` — Full init: migrate + seed + storage:link + optimize
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

**All seeders use `firstOrCreate` (or equivalent skip-if-exists logic) and are safe to re-run without overwriting user-modified data.** `PricingSeeder` checks `PricingCategory::count() > 0` and returns early if data exists.

### Frontend SCSS Structure

```
resources/css/frontend/
├── mh-studio.scss     ← Entry point (@use all partials)
├── _variables.scss    ← CSS custom properties (colors, fonts, shadows)
├── _base.scss         ← Reset, html root font scaling, animations, section common styles
├── _layout.scss       ← Nav (with scrolled state), hero, stats, footer, large screen layout
├── _sections.scss     ← Services, portfolio, process, tech, contact, social embed, pricing, CTA, large screen layout
└── _pages.scss        ← Inner page styles (blog, portfolio, about, quote, service detail, article, large screen layout)
```

### Frontend CSS Conventions (WCAG Compliant)

- **Units**: `rem` for all font sizes, `px` only for borders/shadows/spacing. Minimum font-size: `0.875rem` (14px).
- **Root font scaling**: `html` font-size scales with viewport via media queries: 16px (default) → 17px (1440px+) → 18px (1920px+) → 20px (2560px+). All `rem` values auto-scale.
- **Large screen containers**: Inner page containers (blog, portfolio, about, quote) widen at 1920px+ and 2560px+ via `@media` rules at the end of each SCSS file. Only layout/widths change — font sizes are handled by root scaling.
- **Nav scrolled state**: `.nav.scrolled` reduces height from 80px to 64px, shrinks logo icon/text with `transition: 0.3s ease`.

### Caching Strategy

- **Settings**: Cached forever (`Cache::rememberForever`), auto-invalidated on model save/delete
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

### User Model Password Cast

`User` model has `'password' => 'hashed'` in its `casts()`. Do **NOT** use `Hash::make()` when creating/updating users — Laravel's cast handles hashing automatically. Double-hashing will make passwords unusable.

### CSP and Embedded Content

`SecurityHeaders` middleware includes `frame-src` whitelist for YouTube, Instagram, and LINE. When adding new embedded content sources, update the CSP in `app/Http/Middleware/SecurityHeaders.php`.

### Blade @json with Closures

Never pass PHP closures or complex expressions directly into `@json()` — the Blade compiler cannot parse array brackets `[]` inside closures within `@json()`, causing `ParseError: Unclosed '['`. Instead, prepare data in a `@php` block first, then pass the simple variable to `@json()`.

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
- Media picker: `@include('admin.media.partials.picker-modal')` + `onclick="openMediaPicker('inputId')"`

### Seeder Safety

All seeders use `firstOrCreate` or early-return patterns. They are safe to re-run via `php artisan db:seed` or deploy routes without overwriting user-modified data. **Never use `updateOrCreate` or `delete()` + recreate patterns in seeders.**

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

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Mini WP GDPR is a WordPress plugin (v2.0.0) providing cookie consent popups and tracker script delay-loading for GDPR compliance. It integrates Google Analytics, Facebook Pixel, and Microsoft Clarity, with a filter-based API for registering custom trackers. Optional integrations with WooCommerce and Contact Form 7.

- **Namespace:** `Mini_Wp_Gdpr`
- **Constant prefix:** `PP_MWG_`
- **Text domain:** `mini-wp-gdpr`
- **Global instance:** `$pp_mwg_plugin`
- **Requirements:** WordPress 6.4+, PHP 8.0+

## Commands

```bash
# Linting & static analysis (run before every commit)
phpcs .               # Check WordPress coding standards violations
phpcbf .              # Auto-fix PHPCS violations
phpstan analyse       # Static analysis (Level 5, config in phpstan.neon)

# JavaScript build (Terser minification — no package.json)
node bin/build.js     # Minify all JS files → .min.js + source maps
```

`phpcs`, `phpcbf`, `phpstan`, and `terser` are all installed globally (not via Composer or local npm). Node/npm available via nvm. Config files: `phpcs.xml` and `phpstan.neon`.

## Architecture

**Bootstrap chain** (`mini-wp-gdpr.php`): loads constants → functions → functions-private → core classes → tracker implementations → instantiates `Plugin` class.

**Core classes** (`includes/`):
- `class-plugin.php` — Main orchestrator, hook registration, lazy-loads other components
- `class-settings.php` — WordPress Settings API registration, admin page rendering (instantiated early, before `admin_init`)
- `class-script-blocker.php` — Delays tracker scripts until user consents
- `class-tracker-registry.php` — Registers/manages tracker integrations
- `class-user-controller.php` — Tracks per-user consent acceptance/rejection
- `class-admin-hooks.php` / `class-public-hooks.php` — Asset enqueueing for admin/frontend
- `class-cf7-helper.php` — Contact Form 7 GDPR checkbox integration

**Trackers** (`trackers/`): Google Analytics, Facebook Pixel, Microsoft Clarity — each registered via the `mwg_register_tracker` filter.

**Templates**: `admin-templates/` (8 files) and `public-templates/` (1 file) — all use code-first `printf()`/`echo`, never inline HTML.

**Constants** (`constants.php`): All option keys (`OPT_*`), defaults (`DEF_*`), meta keys, AJAX action names, and rate-limit values.

**Assets** (`assets/`): 4 JS files + CSS. Production uses `.min.js`; `SCRIPT_DEBUG` loads source files.

## Non-Negotiable Coding Rules

Read `AGENTS.md` and `.github/copilot-instructions.md` before making changes. Key rules:

- **No `declare(strict_types=1)`** — breaks WordPress/WooCommerce type boundaries
- **Single-Entry Single-Exit (SESE)** — one `return` per function, no early returns
- **No magic strings/numbers** — define in `constants.php` with `OPT_`/`DEF_` prefixes
- **Dates as `Y-m-d H:i:s T`** — never Unix timestamps
- **Boolean options** — use `filter_var($val, FILTER_VALIDATE_BOOLEAN)`
- **Security** — sanitize all input (`wp_unslash()` + sanitize functions), escape all output (`esc_html`, `esc_attr`, `esc_url`), verify nonces, check capabilities
- **No inline HTML in templates** — use `printf()`/`echo` only
- **No inline JavaScript** — load via `wp_enqueue_script()`
- **WooCommerce orders** — use `WC_Order` methods, never `get_post_meta()`

## Commit Format

```
[M<number>] type: brief description

- Detail bullet
- Additional context
```

Types: `feat:` `fix:` `chore:` `refactor:` `docs:` `style:` `test:`

## Key Reference Documents

- `dev-notes/patterns/` — Detailed implementation patterns (admin tabs, caching, database, JS, settings API, templates, WooCommerce)
- `dev-notes/hooks-and-filters.md` — All 15 hooks/filters with examples
- `dev-notes/developer-guide.md` — Extension points and public API
- `dev-notes/00-project-tracker.md` — Milestone tracking (M1–M10 complete, M11 planned)

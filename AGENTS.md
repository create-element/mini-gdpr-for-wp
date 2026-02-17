# AGENTS.md — Mini WP GDPR

**Plugin:** Mini WP GDPR (`mini-wp-gdpr`)  
**Namespace:** `PP_MWG`  
**Constant prefix:** `PP_MWG_`  
**Text domain:** `mini-wp-gdpr`

This file is for AI coding agents working on this plugin. Read it before making any changes.

---

## Start Here

The full coding standards and patterns live in two places. Read both before implementing anything:

| Document | Purpose |
|---|---|
| [`.github/copilot-instructions.md`](.github/copilot-instructions.md) | Core PHP standards, security rules, WordPress integration patterns |
| [`dev-notes/patterns/README.md`](dev-notes/patterns/README.md) | Index of all detailed pattern reference files |

---

## Non-Negotiable Rules

These override any general instincts you have about "clean code":

### PHP
- **No `declare(strict_types=1);`** — WordPress doesn't use it; it causes type errors at the WP/WooCommerce boundary
- **Single-Entry Single-Exit (SESE)** — functions have one `return` at the end; no early returns
- **Magic strings and numbers → `constants.php`** — prefix defaults with `DEF_`, options keys with `OPT_`
- **Dates as human-readable strings** — `Y-m-d H:i:s T` format, never Unix timestamps
- **Boolean options via `filter_var()`** — handles `'1'`, `'yes'`, `'on'`, `true`, etc.
- **Security everywhere** — sanitize input (`sanitize_text_field`, `absint`, etc.), escape output (`esc_html`, `esc_attr`, etc.), verify nonces, check capabilities

### Templates
- **No inline HTML** — all template files (`admin-templates/`, `public-templates/`) must be code-first using `printf()` or `echo`; never mix HTML markup with PHP snippets
- **No inline JavaScript** — load scripts via `wp_enqueue_script()`

### Protected Directories
- **`pp-core.php` and `pp-assets/`** — being removed in Milestone 3; do not touch or refactor them
- **Never run PHPCS on them** — they are excluded in `phpcs.xml`

---

## Plugin Structure

```
mini-gdpr-for-wp/
├── mini-wp-gdpr.php          # Main plugin file — hooks, require chain
├── constants.php             # All PP_MWG_ constants (DEF_ and OPT_ prefixes)
├── functions.php             # Public-facing helper functions
├── functions-private.php     # Internal helpers
├── phpcs.xml                 # PHPCS config — check this before running linters
├── includes/                 # Core classes (class-*.php naming)
├── admin-templates/          # Admin UI templates — code-first printf/echo only
├── public-templates/         # Front-end templates — same rule
├── assets/
│   ├── admin/                # Admin CSS/JS
│   └── public/               # Public CSS/JS
├── trackers/                 # Tracker integrations (Facebook Pixel, GA, Clarity, etc.)
├── languages/                # Translation files
├── dev-notes/                # Development documentation (see below)
└── pp-core.php / pp-assets/  # ⛔ Third-party — DO NOT TOUCH
```

---

## Development Workflow

Full workflow: [`dev-notes/workflows/development-workflow.md`](dev-notes/workflows/development-workflow.md)

### Quick cycle

```bash
# Edit code
bin/fix.sh       # Auto-fix PHPCS issues (phpcbf)
bin/check.sh     # Run PHPCS + PHPUnit — must pass before committing
```

Composer aliases also work: `composer run lint` / `composer run format` / `composer run test`.

### Commit message format

Commits use milestone prefixes:

```
[M2] fix: brief description of what changed

- Bullet explaining why or what was tricky
- Additional context if needed
```

**Types:** `feat:` `fix:` `chore:` `refactor:` `docs:` `style:` `test:`

Full commit guide: [`dev-notes/workflows/commit-to-git.md`](dev-notes/workflows/commit-to-git.md)

---

## Pattern Reference

Consult these before implementing any of the following:

| Pattern | File |
|---|---|
| Hash-based admin tabs | [`dev-notes/patterns/admin-tabs.md`](dev-notes/patterns/admin-tabs.md) |
| Transients / rate limiting | [`dev-notes/patterns/caching.md`](dev-notes/patterns/caching.md) |
| Custom DB tables & migrations | [`dev-notes/patterns/database.md`](dev-notes/patterns/database.md) |
| Modern JS, AJAX | [`dev-notes/patterns/javascript.md`](dev-notes/patterns/javascript.md) |
| Settings API, meta boxes | [`dev-notes/patterns/settings-api.md`](dev-notes/patterns/settings-api.md) |
| Template loading with overrides | [`dev-notes/patterns/templates.md`](dev-notes/patterns/templates.md) |
| WooCommerce / HPOS | [`dev-notes/patterns/woocommerce.md`](dev-notes/patterns/woocommerce.md) |

---

## Code Quality Checks

Run these before every commit — **never commit failing checks**:

```bash
bin/check.sh          # Full check: PHPCS + PHPUnit
bin/fix.sh            # Auto-fix PHPCS violations first
bin/test.sh           # PHPUnit only
bin/test.sh --coverage  # With HTML coverage report in coverage/
```

PHPCS config is in `phpcs.xml`. It excludes `pp-core.php`, `pp-assets/`, `vendor/`, `dev-notes/`.

---

## Key Facts About This Plugin

- **What it does:** Cookie consent popup + tracker script delay-loading for GDPR compliance
- **Tracker integrations:** Facebook Pixel, Google Analytics, Microsoft Clarity (in `trackers/`)
- **Commercial plugin:** Uses Power Plugins licence controller (`pp-core.php`) — sealed, don't touch
- **Current milestone:** M2 — Code Standards & Testing Setup
- **Active dev branch:** See git log; use feature branches for all new work

---

## What NOT to Do

- ❌ Modify anything in `pp-core.php` or `pp-assets/`
- ❌ Use `declare(strict_types=1);`
- ❌ Use multiple `return` statements in a function
- ❌ Put magic strings/numbers inline — use `constants.php`
- ❌ Use `get_post_meta()` for WooCommerce order data
- ❌ Store Unix timestamps in options
- ❌ Write inline HTML in template files
- ❌ Write inline JavaScript
- ❌ Commit without passing `bin/check.sh`

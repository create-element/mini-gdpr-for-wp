# Development Workflow

This document outlines the complete development workflow for Mini WP GDPR,
from setting up your environment to committing and pushing code changes.

---

## Table of Contents

1. [Initial Setup](#initial-setup)
2. [Daily Development Workflow](#daily-development-workflow)
3. [Code Quality Tools](#code-quality-tools)
4. [Testing](#testing)
5. [Committing Changes](#committing-changes)
6. [Pre-Commit Checklist](#pre-commit-checklist)
7. [Troubleshooting](#troubleshooting)

---

## Initial Setup

### Prerequisites

- PHP 7.4 or higher
- PHP_CodeSniffer (PHPCS) installed globally with WordPress Coding Standards
- A local WordPress environment (this project uses `westfield.local`)
- Git

### Verify PHPCS Setup

```bash
phpcs --version
phpcs -i | grep WordPress
```

You should see PHPCS version and "WordPress, WordPress-Core, WordPress-Docs, WordPress-Extra" in the installed standards list.

If WPCS is not installed, install it globally:

```bash
composer global require squizlabs/php_codesniffer wp-coding-standards/wpcs
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
```

### JavaScript Build Tools

The plugin uses a `package.json` (dev-only) for the JavaScript minification pipeline.

```bash
# Install build tools (once, or after a fresh clone)
npm install

# Build all minified JS assets
npm run build
```

This runs `bin/build.js` which uses Terser to produce `.min.js` files in `assets/`.
The `.min.js` files are committed to the repository (plugin users do not need Node.js).
Source maps (`*.min.js.map`) are excluded from git (see `.gitignore`).

### Note: No composer.json in This Plugin

This plugin does not use Composer or a `vendor/` directory. Shell scripts (bin/) were removed as a security risk — they have no place in a WordPress plugin. Use the globally-installed `phpcs` and `phpcbf` commands directly.

---

## Daily Development Workflow

### Standard Development Cycle

1. **Check out the correct branch** (main for now; feature branches for larger work):
   ```bash
   git branch --show-current
   ```

2. **Make your changes**, following the coding standards in `.github/copilot-instructions.md` and the patterns in `dev-notes/patterns/`.

3. **Auto-fix PHPCS style issues:**
   ```bash
   phpcbf --standard=phpcs.xml .
   ```

4. **Check for remaining PHPCS issues:**
   ```bash
   phpcs --standard=phpcs.xml .
   ```
   Fix any remaining issues manually. Do not commit with outstanding errors.

5. **Manual testing** — see [Testing](#testing) below.

6. **Commit your changes** using the milestone prefix format:
   ```bash
   git add .
   git commit -m "[M3] fix: brief description"
   ```

7. **Push to remote after confirmed-passing testing sprint:**
   ```bash
   git push
   ```

---

## Code Quality Tools

Mini WP GDPR uses PHP_CodeSniffer with the WordPress Coding Standards, and Terser for JavaScript minification.

### JavaScript Builds

After modifying any `.js` file in `assets/`, rebuild the minified assets:

```bash
npm run build
```

Commit both the source `.js` and the minified `.min.js` together. The PHP enqueue
functions use `SCRIPT_DEBUG` to switch between them:

```php
$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
wp_enqueue_script( 'handle', ASSETS_URL . "file$suffix.js", ... );
```

Set `define( 'SCRIPT_DEBUG', true );` in `wp-config.php` for development.

---

### Running PHPCS

```bash
# Check code style (from plugin root)
phpcs --standard=phpcs.xml .

# Auto-fix fixable style issues
phpcbf --standard=phpcs.xml .

# Check a specific file
phpcs --standard=phpcs.xml includes/class-settings.php
```

The PHPCS configuration lives in `phpcs.xml`. It excludes:

- `dev-notes/` (development documentation, not plugin code)
- `vendor/` (not present in this plugin)
- `languages/` (translation files)
- `tests/` (if added)
- `*.js`, `*.css` (JavaScript and CSS handled separately in M4+)

### What PHPCS Checks

- WordPress Coding Standards (tabs, braces, spacing)
- Security rules (nonce verification, input sanitisation, output escaping)
- Text domain consistency (`mini-wp-gdpr`)
- Global namespace prefix requirements (`pp_mwg_`, `PP_MWG_`, `mwg_`, `Mini_Wp_Gdpr\`)
- Minimum WordPress version compatibility (5.0+)

---

## Testing

There are no automated unit tests for this plugin. Testing is manual.

### Manual Testing Protocol (WordPress Plugin)

After making changes, verify:

1. **Plugin is active** — check the Plugins list at `/wp-admin/plugins.php`
2. **Admin loads cleanly** — visit `/wp-admin/` and check for PHP errors in the admin bar or page
3. **Settings page works** — visit `/wp-admin/options-general.php?page=minigdpr`, check the form renders and saves correctly
4. **Front-end loads cleanly** — visit the site front-end (e.g. `http://westfield.local/`) and verify no visible errors
5. **Error log is clean** — check the site error log:
   ```bash
   tail -20 /var/www/westfield.local/log/error.log
   ```
6. **Debug log is clean** — check WordPress debug log:
   ```bash
   tail -20 /var/www/westfield.local/web/wp-content/debug.log
   ```

### Running WP-CLI Checks

```bash
# Verify plugin is active
wp --path=/var/www/westfield.local/web plugin list --name=mini-gdpr-for-wp

# Check for fatal errors via eval
wp --path=/var/www/westfield.local/web eval 'echo "OK";'
```

### PHPStan (Deferred to Milestone 8)

PHPStan static analysis is scheduled for M8, after the bulk of refactoring in
M3–M6 is complete. Running PHPStan on code that is still being rewritten creates
noise without value.

When M8 arrives, set up PHPStan:

```bash
composer global require phpstan/phpstan
phpstan analyse --configuration phpstan.neon
```

---

## Committing Changes

### Commit Message Format

Use milestone prefixes for all commits:

```
[M3] type: brief description of changes

- Bullet explaining why or what was tricky
- Additional context if needed
```

**Types:** `feat:` `fix:` `chore:` `refactor:` `docs:` `style:` `test:`

Full commit guide: [`commit-to-git.md`](commit-to-git.md)

### What to Commit

**Do commit:**
- Source code changes (PHP, JS, CSS)
- Documentation updates (`dev-notes/`, `README.md`, `CHANGELOG.md`)
- Configuration files (`phpcs.xml`, `.editorconfig`, `.gitignore`)

**Do NOT commit:**
- `vendor/` (not used — no composer.json)
- `node_modules/` (local build tools — excluded by `.gitignore`)
- `*.min.js.map` (source maps — excluded by `.gitignore`)
- IDE-specific files (`.idea/`, `.vscode/` unless shared config)
- Temporary files, cache files, coverage reports

The `.gitignore` handles most of this automatically.

---

## Pre-Commit Checklist

Before committing:

1. **Run PHPCBF** to auto-fix style issues:
   ```bash
   phpcbf --standard=phpcs.xml .
   ```

2. **Run PHPCS** to check for remaining issues:
   ```bash
   phpcs --standard=phpcs.xml .
   ```
   → **Must show 0 errors.** Warnings are acceptable if well-justified.

3. **Manual test** the settings page and front-end (see Testing section).

4. **Check error logs** are clean.

5. **Commit** using the correct milestone prefix format.

---

## Troubleshooting

### "phpcs: command not found"

Install PHPCS globally:

```bash
composer global require squizlabs/php_codesniffer wp-coding-standards/wpcs
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
```

Or check if it's installed elsewhere:

```bash
which phpcs
find / -name phpcs 2>/dev/null
```

### "WordPress standard not found"

```bash
phpcs --config-set installed_paths /path/to/wpcs
phpcs -i
```

### "Plugin not appearing in WP admin"

Check that the plugin header in `mini-wp-gdpr.php` is valid:

```bash
wp --path=/var/www/westfield.local/web plugin list
```

Check the error log for PHP parse errors:

```bash
tail -50 /var/www/westfield.local/log/error.log
```

### "PHPCS shows errors in dev-notes/ or vendor/"

These directories are excluded in `phpcs.xml`. If PHPCS scans them anyway:

```bash
phpcs --standard=phpcs.xml --report=summary .
```

Check that you are running `phpcs` from the plugin root directory where `phpcs.xml` lives.

---

## Development Phases by Milestone

### Milestone 2 (Complete): Code Standards & Quality Tools (PHPCS)
- Set up phpcs.xml, .editorconfig, and documented the development workflow.

### Milestone 3 (Current): Remove pp-core.php Dependency
- Major refactoring: replace pp-core.php with native plugin classes.
- Workflow: Small commits, frequent manual testing, preserve backwards compatibility.
- Extra checks: Test settings save/load, verify no breaking changes in error log.

### Milestone 4: JavaScript Modernization
- Focus: ES6+ refactoring, build process, minification.
- Tools: Terser via `npm run build` (Phase 4.4).
- Workflow: Refactor → `npm run build` (if JS changed) → manual test in browser.

### Milestones 5-6: New Features (Consent Management & Tracker Delay-Loading)
- Focus: Add Reject button, improve delay-loading.
- Workflow: Code → PHPCS → manual browser testing → privacy verification.

### Milestone 7: Security Audit
- Focus: Security hardening — comprehensive code review.
- Workflow: Audit → fix → verify → document.

### Milestone 8: PHPStan, Testing & QA
- Focus: PHPStan analysis, comprehensive manual testing.
- Workflow: PHPStan baseline → fix critical issues → browser testing.

### Milestones 9-10: Documentation & Release
- Focus: Finalise documentation, prepare release package.
- Workflow: Document → review → package → release.

---

## Quick Reference

| Task                          | Command                                     |
|-------------------------------|---------------------------------------------|
| Check code style              | `phpcs --standard=phpcs.xml .`              |
| Auto-fix style issues         | `phpcbf --standard=phpcs.xml .`             |
| Check a single file           | `phpcs --standard=phpcs.xml <file.php>`     |
| Build minified JS assets      | `npm run build`                             |
| View plugin in WP             | `wp --path=... plugin list`                 |
| Tail error log                | `tail -f /var/www/westfield.local/log/error.log` |
| Tail debug log                | `tail -f /var/www/.../wp-content/debug.log` |

---

**Last Updated:** 17 February 2026
**Milestone:** M4 (JavaScript Modernisation — Phase 4.4 Complete)

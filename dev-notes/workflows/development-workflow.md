# Development Workflow

This document outlines the complete development workflow for Mini WP GDPR, from setting up your environment to committing code changes.

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
- Composer installed globally
- WordPress development environment (local or remote)
- Git

### One-Time Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd mini-gdpr-for-wp
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up WordPress test suite** (for PHPUnit):
   ```bash
   bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```
   
   Replace `root`, `''`, and `localhost` with your MySQL credentials if different.

4. **Verify setup:**
   ```bash
   bin/check.sh
   ```

---

## Daily Development Workflow

### Standard Development Cycle

1. **Create or switch to a feature branch:**
   ```bash
   git checkout -b feature/my-feature
   ```

2. **Make your changes:**
   - Edit code following WordPress Coding Standards
   - See [code-standards.md](code-standards.md) for details

3. **Run code quality checks frequently:**
   ```bash
   bin/check.sh
   ```

4. **Auto-fix style issues:**
   ```bash
   bin/fix.sh
   ```

5. **Write or update tests:**
   - Add PHPUnit tests for new functionality
   - Update existing tests if behaviour changes
   - See [Testing](#testing) section below

6. **Run tests:**
   ```bash
   bin/test.sh
   ```

7. **Commit your changes:**
   ```bash
   git add .
   git commit -m "[M#] Brief description of changes"
   ```
   
   See [commit-to-git.md](commit-to-git.md) for commit message conventions.

8. **Push to remote:**
   ```bash
   git push origin feature/my-feature
   ```

---

## Code Quality Tools

Mini WP GDPR uses several tools to maintain code quality:

### Shell Scripts (Recommended)

Located in `bin/`, these scripts wrap Composer commands for convenience:

- **`bin/check.sh`** - Run all checks (PHPCS + PHPUnit)
- **`bin/fix.sh`** - Auto-fix code style issues (PHPCBF)
- **`bin/test.sh`** - Run PHPUnit tests
- **`bin/test.sh --coverage`** - Run tests with code coverage report

**Example workflow:**
```bash
# Make changes
vim includes/class-settings.php

# Auto-fix style issues
bin/fix.sh

# Check everything
bin/check.sh

# If tests fail, fix and re-check
bin/check.sh
```

### Composer Scripts (Alternative)

You can also use Composer commands directly:

```bash
# Run PHPCS (check code style)
composer run-script phpcs

# Run PHPCBF (auto-fix code style)
composer run-script phpcbf

# Run PHPUnit tests
composer run-script test

# Shorthand aliases
composer run format      # Same as phpcbf
composer run lint        # Same as phpcs
```

### PHPCS (PHP CodeSniffer)

- **What it does:** Checks code against WordPress Coding Standards
- **Config file:** `phpcs.xml`
- **Run manually:** `bin/check.sh` or `composer run phpcs`
- **Auto-fix:** `bin/fix.sh` or `composer run phpcbf`

**PHPCS ignores:**
- `pp-core.php` (being removed in Milestone 3)
- `pp-assets/` (being removed in Milestone 3)
- `dev-notes/`, `vendor/`, `node_modules/`
- JavaScript and CSS files (covered by separate linters in M4)

### PHPUnit

- **What it does:** Runs automated unit tests
- **Config file:** `phpunit.xml.dist`
- **Run manually:** `bin/test.sh` or `composer run test`
- **Test directory:** `tests/`

---

## Testing

### Running Tests

**Quick test run:**
```bash
bin/test.sh
```

**With code coverage:**
```bash
bin/test.sh --coverage
```

This generates an HTML coverage report in `coverage/index.html`.

### Writing Tests

All tests go in the `tests/` directory, mirroring the structure of the main codebase:

```
tests/
├── bootstrap.php                    # Test bootstrap (auto-loaded)
├── test-sample.php                 # Example test (can be deleted)
├── class-settings-test.php         # Tests for Settings class
└── class-user-controller-test.php  # Tests for User_Controller class
```

**Example test:**
```php
<?php
/**
 * Tests for the Settings class.
 *
 * @package Mini_WP_GDPR
 */

class Settings_Test extends WP_UnitTestCase {
    
    public function test_settings_registration() {
        // Arrange
        $settings = new PP_MWG\Settings();
        
        // Act
        $settings->register();
        
        // Assert
        $this->assertTrue( has_action( 'admin_init', [ $settings, 'register_settings' ] ) );
    }
}
```

### Test Database

PHPUnit uses a separate test database (`wordpress_test` by default) which is created and torn down automatically. Never use your development database for testing.

---

## Committing Changes

### Commit Message Format

Use milestone prefixes for all commits:

```
[M1] Brief description of changes
[M2] Brief description of changes
[M3] Brief description of changes
```

See [commit-to-git.md](commit-to-git.md) for full details.

### What to Commit

**Do commit:**
- Source code changes
- Test files
- Documentation updates
- Configuration files (phpcs.xml, composer.json, etc.)

**Don't commit:**
- `vendor/` (managed by Composer)
- `node_modules/` (managed by npm, when added in M4)
- Coverage reports (`coverage/`)
- IDE-specific files (.idea/, .vscode/ unless shared config)
- Temporary files

The `.gitignore` file handles most of this automatically.

---

## Pre-Commit Checklist

Before committing, **always** run:

```bash
bin/check.sh
```

This ensures:
- ✅ Code follows WordPress Coding Standards
- ✅ All tests pass
- ✅ No syntax errors

**If checks fail:**

1. **PHPCS issues:** Run `bin/fix.sh` to auto-fix most style issues
2. **Remaining PHPCS issues:** Manually fix issues reported by `bin/check.sh`
3. **Test failures:** Debug and fix failing tests

**Never commit code that fails checks** unless you have a very good reason and document it.

---

## Troubleshooting

### "Composer not found"

Install Composer globally: https://getcomposer.org/download/

### "Vendor directory not found"

Run:
```bash
composer install
```

### "WordPress test library not found"

Run:
```bash
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

Adjust MySQL credentials as needed.

### "PHPCS shows errors in pp-core.php"

This shouldn't happen (it's excluded in phpcs.xml). If you see this:

1. Check that `phpcs.xml` contains:
   ```xml
   <exclude-pattern>pp-core.php</exclude-pattern>
   <exclude-pattern>pp-assets/*</exclude-pattern>
   ```

2. Clear PHPCS cache:
   ```bash
   composer run phpcs -- --cache-clear
   ```

### "Tests fail with database connection error"

1. Verify MySQL is running
2. Check credentials in `bin/install-wp-tests.sh`
3. Re-run the install script with correct credentials
4. Verify `/tmp/wordpress-tests-lib/` exists and contains WordPress test files

### "bin/check.sh says permission denied"

Make scripts executable:
```bash
chmod +x bin/*.sh
```

---

## Development Phases by Milestone

Different milestones have different workflows:

### Milestone 2 (Current): Code Standards & Testing Setup
- Focus: Set up tooling and establish baselines
- Key files: `phpcs.xml`, `composer.json`, `phpunit.xml.dist`, `bin/*.sh`
- Workflow: Install tools → configure → baseline → document

### Milestone 3: Remove pp-core.php
- Focus: Major refactoring
- Workflow: Small commits, frequent testing, preserve backwards compatibility
- Extra checks: Test settings save/load, verify no breaking changes

### Milestone 4: JavaScript Modernization
- Focus: ES6+ refactoring
- New tools: ESLint, Prettier (to be added)
- Workflow: Refactor → lint → test in browser

### Milestone 5-6: New Features
- Focus: Consent management & tracker delay-loading
- Workflow: Write tests first (TDD), then implement features
- Extra checks: Manual browser testing, privacy verification

### Milestone 7: Security Audit
- Focus: Security hardening
- Tools: Manual code review, security scanning tools
- Workflow: Audit → fix → verify → document

### Milestone 8: QA
- Focus: Comprehensive testing
- Workflow: Write missing tests → integration tests → browser testing

### Milestone 9-10: Documentation & Release
- Focus: Finalize documentation, prepare release
- Workflow: Document → review → package → release

---

## Quick Reference

| Task | Command |
|------|---------|
| Install dependencies | `composer install` |
| Run all checks | `bin/check.sh` |
| Auto-fix style | `bin/fix.sh` |
| Run tests | `bin/test.sh` |
| Coverage report | `bin/test.sh --coverage` |
| Check PHPCS only | `composer run phpcs` |
| Fix PHPCS only | `composer run phpcbf` |
| Run PHPUnit only | `composer run test` |

---

**Last Updated:** 16 February 2026  
**Milestone:** M2 (Code Standards & Testing Setup)

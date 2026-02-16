# Archive Directory

**Purpose:** Placeholder for future archival of pp-core.php components during v2.0 refactoring

⚠️ **IMPORTANT:** This directory is currently EMPTY and serves as a placeholder.  
`pp-core.php` and `pp-assets/` are still ACTIVE in the plugin root and should NOT be removed until Milestone 3.

---

## Planned Contents (Milestone 3)

### pp-core.php (TO BE ARCHIVED)
**Current Location:** `/pp-core.php` (2305 lines) - **STILL ACTIVE**  
**License:** MIT License  
**Description:** Power Plugins Core framework currently used in v1.4.3

This file provides:
- `Component` base class for plugin components
- `Settings_Core` base class for settings management
- Utility functions for admin UI
- Meta box management
- Form rendering helpers
- Branding elements

**Will Be Removed In:** Milestone 3 (after replacement implementation)

**Reason for Future Removal:**
- Moving to native WordPress Settings API
- Reducing external dependencies
- Simplifying codebase maintenance
- Better adherence to WordPress Coding Standards

**Will Be Preserved For:**
- Reference during refactoring
- Understanding legacy functionality
- Migration of useful patterns
- Historical documentation

### pp-assets/ (TO BE ARCHIVED)
**Current Location:** `/pp-assets/` - **STILL ACTIVE**  
**Description:** CSS and JavaScript assets for Power Plugins Core framework

Contains:
- `pp-admin.css` - Admin styling
- `pp-admin.js` - Admin JavaScript
- `pp-public.css` - Public-facing styles
- `pp-public.js` - Public-facing JavaScript
- `index.php` - Directory protection
- `pp-logo.png` - Branding
- `spinner.svg` - Loading indicator

**Will Be Removed In:** Milestone 3 (after replacement implementation)

**Reason for Future Removal:**
- No longer needed without pp-core.php
- Replaced with plugin-specific assets
- Reducing asset bloat

---

## Migration Notes

### From Settings_Core

The `Settings_Core` class provided the following that needs replacement:

**Methods to Replace:**
- `get_bool()` - Get boolean option value
- `get_string()` - Get string option value
- `set_bool()` - Set boolean option value
- `set_string()` - Set string option value
- `maybe_save_settings()` - Check and save settings
- `open_wrap()` / `close_wrap()` - Admin page wrapper
- `open_form()` / `close_form()` - Settings form wrapper

**Implementation Strategy:**
- Use `get_option()` with type casting for retrieving values
- Use `update_option()` for saving values
- Use WordPress Settings API (`register_setting()`, `add_settings_section()`, `add_settings_field()`)
- Manually implement form HTML or use settings API callbacks

### From Component

The `Component` base class provided:

**Properties:**
- `$name` - Plugin name
- `$version` - Plugin version
- `$settings` - Settings controller reference

**Implementation Strategy:**
- Pass name/version directly to classes if needed
- Use global function or singleton pattern for settings access
- Consider dependency injection for better testability

---

## Reference Links

**Original Power Plugins Site:** https://power-plugins.com/  
**pp-core License:** MIT License (see file header in pp-core-v1.4.3.php)

---

## Important Notes

⚠️ **DO NOT ARCHIVE FILES YET**

This directory is a placeholder. pp-core.php and pp-assets/ should remain in the plugin root until:

1. **Milestone 2 Complete:** Code standards and tooling setup
2. **Milestone 3 In Progress:** Replacement implementation underway
3. **Testing Complete:** New implementation verified working
4. **THEN Archive:** Move files here for historical reference

**Timeline:**
- **Now (Milestone 1):** Planning phase - files remain active
- **Milestone 2:** Code standards setup - files remain active
- **Milestone 3:** Replacement implementation - files remain active until replacements tested
- **Late Milestone 3:** Archive files once confirmed no longer needed

---

**Directory Created:** 16 February 2026  
**Files To Archive:** pp-core.php, pp-assets/  
**Archive Date:** TBD (Milestone 3)  
**Current Status:** PLACEHOLDER ONLY

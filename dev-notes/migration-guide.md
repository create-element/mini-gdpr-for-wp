# Migration Guide: v1.4.3 → v2.0.0

**Last Updated:** 18 February 2026  
**From:** Mini WP GDPR v1.4.3  
**To:** Mini WP GDPR v2.0.0

This guide is for developers and site owners upgrading from v1.4.3 to v2.0.0.

---

## TL;DR

For most sites:
- **All existing settings are preserved** — no data migration needed
- **All existing `wp_options` keys still work** — no breakage
- **Deactivate → Upload → Activate** is the full upgrade procedure
- There are **no breaking changes** for end users

For developers with custom code:
- The `pp-core.php` framework has been removed; a handful of function names changed
- jQuery is no longer a dependency of the plugin's JavaScript
- New hooks are available (see [hooks-and-filters.md](hooks-and-filters.md))

---

## What's New in v2.0.0

### 1. "Reject" Button Added to Consent Popup

The consent popup now has three buttons: **Reject** | **Cookie info…** | **Accept**

This is required for GDPR compliance. Users can now explicitly reject tracking, and a floating 🍪 button appears afterward so they can change their decision later.

**No action required** — the new button appears automatically after upgrading.

**New settings available** in Settings → Mini WP GDPR → Cookie Consent:
- Reject button label (customizable text)
- Info button label (customizable text)
- Accept button label (customizable text — previously fixed)

---

### 2. Rejection Tracking for Logged-In Users

When a logged-in user clicks "Reject", their rejection is recorded in WordPress user meta (key: `_pwg_rejected_gdpr_when`). The consent stats dashboard now shows Accepted / Rejected / Undecided counts.

**New developer hook:** `mwg_consent_rejected` (see [hooks-and-filters.md](hooks-and-filters.md)).

---

### 3. Google Consent Mode v2

When Google Analytics is configured, the plugin now automatically outputs Google Consent Mode v2 defaults in `<head>` with all signals set to `denied`. On user consent, signals are updated to `granted` before the GA SDK loads.

**Enable it:** Settings → Mini WP GDPR → Google Analytics → Enable Google Consent Mode v2.

This is highly recommended if you use Google Analytics — it signals consent intent to Google's infrastructure even before the user decides.

---

### 4. Improved Tracker Delay-Loading

All built-in trackers (GA, Facebook Pixel, Microsoft Clarity) now load **only after explicit user consent**:

- **Google Analytics** — GA4 SDK injected dynamically after consent; Consent Mode v2 signals fire first
- **Facebook Pixel** — `fbq('consent','revoke')` in stub; SDK + `fbq('consent','grant')` after consent
- **Microsoft Clarity** — Clarity SDK injected dynamically; window.clarity queue replayed on load

Previously, some trackers could load on returning visitors before the consent check completed. This is fixed in v2.0.0.

---

### 5. Custom Tracker Registration API

A new `mwg_register_tracker` filter lets developers register arbitrary third-party trackers. Registered trackers are automatically wired into:
- Server-side script blocking
- The "Cookie information" overlay (description shown to users)
- JavaScript delay-loading after consent (`loadCustomTrackers()`)

See [hooks-and-filters.md](hooks-and-filters.md) and [tracker-registration-api.md](tracker-registration-api.md).

---

### 6. JavaScript Modernization (ES6+)

All JavaScript has been rewritten in ES6+ (no jQuery). The plugin no longer enqueues jQuery as a dependency.

If you had custom JavaScript that used the plugin's functions (e.g. `mgwAcceptCookies()`) via jQuery-ready, update it to use the new public API:
- `window.mgwRejectScripts()` — reject consent
- `window.mgwShowCookiePreferences()` — re-show the consent popup

Minified assets are now built with Terser (52–67% size reduction vs v1.x).

---

### 7. Accessibility Improvements

- Consent popup is now a proper `role="dialog"` with `aria-modal`, `aria-live`, and `aria-describedby`
- Full keyboard Tab trapping inside the popup and the info overlay
- Accept button receives focus automatically when the popup opens
- Focus returns to the "info…" button when the overlay closes
- All interactive elements have descriptive `aria-label` attributes

---

### 8. Security Hardening

- Rate limiting on consent-related AJAX endpoints (10 accept/reject per hour per user; 3 resets per 5 minutes)
- `wp_kses_post()` used for consent message output (preserves admin-configured `<strong>`, `<em>` tags)
- All outputs consistently escaped throughout admin templates
- PHPStan level 5 passes with 0 errors

---

## Upgrade Procedure

### For Most Sites (Standard Upgrade)

1. **Back up your database** (standard precaution)
2. In wp-admin, go to **Plugins → Installed Plugins**
3. Deactivate **Mini WP GDPR**
4. Delete the plugin (your settings in `wp_options` are preserved — they are not deleted on deactivation or deletion)
5. Upload the v2.0.0 `.zip` via **Plugins → Add New → Upload Plugin**
6. Activate the plugin
7. Go to **Settings → Mini WP GDPR** and verify your settings are intact

> ✅ All `wp_options` keys from v1.4.3 carry over unchanged. No data migration is needed.

---

### For Developers with Customizations

If you have custom code that uses the plugin (custom hooks, filters, or JavaScript), review the breaking changes below before upgrading.

---

## Breaking Changes

### PHP: `pp-core.php` Removed

**Who is affected:** Developers who directly called functions from `pp-core.php`.

The `pp-core.php` framework (Power Plugins core library) has been removed. The plugin now uses native WordPress APIs exclusively.

If you had code like:
```php
// ❌ v1.x — pp-core functions (no longer available)
$settings = \PP\Core\get_settings( 'mini-wp-gdpr' );
```

Migrate to the plugin's public API:
```php
// ✅ v2.x — native WordPress Options API
$setting = get_option( 'mwg_is_cookie_consent_popup_enabled', '1' );
```

All option keys are unchanged — see the [wp_options keys reference](#wp_options-keys-unchanged) below.

---

### PHP: `pp-assets/` CSS Removed

**Who is affected:** Sites with custom CSS that targeted `pp-assets/` stylesheet selectors.

The `pp-assets/` admin stylesheet has been replaced with the plugin's own `assets/admin/mwg-admin.css`. If you overrode pp-assets styles in your admin CSS, audit those rules.

---

### JavaScript: jQuery No Longer Enqueued

**Who is affected:** Custom JavaScript that depended on the plugin enqueueing jQuery.

In v1.x, `mini-gdpr.js` listed jQuery as a dependency, which meant jQuery was always loaded on the front-end when the plugin was active. In v2.0.0, jQuery is no longer enqueued by this plugin.

If your code relied on this implicit jQuery enqueue, add an explicit jQuery dependency to your own script:
```php
// ✅ Explicitly declare your own jQuery dependency
wp_enqueue_script( 'my-script', MY_SCRIPT_URL, [ 'jquery' ], '1.0', true );
```

---

### JavaScript: Old Global Function Names

**Who is affected:** Sites with custom JavaScript calling v1.x functions.

| v1.x function | v2.x replacement |
|---------------|------------------|
| `mgwAcceptCookies()` | *(called internally only — no public equivalent needed)* |
| Custom popup show | `window.mgwShowCookiePreferences()` |
| Custom reject | `window.mgwRejectScripts()` |

---

## wp_options Keys (Unchanged)

All settings are stored using the same `wp_options` keys as v1.4.3. Your existing configuration carries over automatically:

| Option Key | Type | Description |
|------------|------|-------------|
| `mwg_is_cookie_consent_popup_enabled` | bool | Enable/disable the consent popup |
| `mwg_always_show_consent` | bool | Always show popup (even to returning visitors) |
| `mwg_consent_duration` | int | How long consent lasts (days) |
| `mwg_is_ga_enabled` | bool | Enable Google Analytics integration |
| `mwg_ga_tracking_code` | string | GA tracking ID (UA-XXXXX or G-XXXXX) |
| `mwg_is_fbpx_enabled` | bool | Enable Facebook Pixel integration |
| `mwg_fbpx_id` | string | Facebook Pixel ID |
| `mwg_is_msft_clarity_enabled` | bool | Enable Microsoft Clarity integration |
| `mwg_msft_clarity_id` | string | Clarity project ID |

**New in v2.0.0** (not present in v1.x — added with defaults on activation):

| Option Key | Type | Default | Description |
|------------|------|---------|-------------|
| `mwg_consent_accept_text` | string | "I accept" | Accept button label |
| `mwg_consent_reject_text` | string | "Reject" | Reject button label |
| `mwg_consent_info_btn_text` | string | "Cookie info…" | Info button label |
| `mwg_ga_consent_mode_enabled` | bool | false | Enable Google Consent Mode v2 |

---

## Upgrade Checklist

Use this checklist when upgrading a production site:

- [ ] Back up database before upgrade
- [ ] Review any custom hooks/filters for compatibility (see [hooks-and-filters.md](hooks-and-filters.md))
- [ ] Check for custom JavaScript calling old v1.x function names
- [ ] Check for custom CSS targeting `pp-assets/` selectors
- [ ] After upgrade: verify Settings → Mini WP GDPR shows expected values
- [ ] After upgrade: check front-end displays consent popup with 3 buttons (Reject, Info, Accept)
- [ ] After upgrade: test Accept flow (trackers load, popup hides, 🍪 button appears)
- [ ] After upgrade: test Reject flow (no trackers load, popup hides, 🍪 button appears)
- [ ] After upgrade: test 🍪 button reopens the popup
- [ ] After upgrade: check WordPress error log is clean
- [ ] After upgrade: verify tracked user consent data is intact (Settings → Mini WP GDPR → Consent Stats)
- [ ] After upgrade: enable Google Consent Mode v2 if you use Google Analytics

---

## Support & Questions

- **GitHub Issues:** Report bugs or compatibility questions on [GitHub](https://github.com/create-element/mini-gdpr-for-wp/issues)
- **Email:** [hello@power-plugins.com](mailto:hello@power-plugins.com)
- **Developer reference:** [hooks-and-filters.md](hooks-and-filters.md)

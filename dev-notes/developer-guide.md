# Developer Guide — Mini WP GDPR v2.0.0

**Last Updated:** 18 February 2026  
**Plugin version:** 2.0.0  
**PHP namespace:** `Mini_Wp_Gdpr`  
**Constant prefix:** `PP_MWG_`  
**Hook prefix:** `mwg_` (public API)

This guide is for developers extending or integrating with Mini WP GDPR. For the public API reference see [`hooks-and-filters.md`](hooks-and-filters.md).

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Plugin Bootstrap](#plugin-bootstrap)
3. [Class Reference](#class-reference)
4. [Constants Reference](#constants-reference)
5. [Registering Custom Trackers](#registering-custom-trackers)
6. [Listening for Consent Events](#listening-for-consent-events)
7. [Reading Consent State](#reading-consent-state)
8. [JavaScript API](#javascript-api)
9. [Settings API](#settings-api)
10. [Admin UI Extension Points](#admin-ui-extension-points)
11. [Coding Standards](#coding-standards)

---

## Architecture Overview

Mini WP GDPR is structured as a classic WordPress plugin with a `Plugin` orchestrator class that wires up all components:

```
mini-wp-gdpr.php              Plugin bootstrap — calls pp_mwg_plugin_run()
constants.php                 All PP_MWG_* constants (OPT_, DEF_, META_)
functions.php                 Public developer API (global namespace)
functions-private.php         Internal helpers (Mini_Wp_Gdpr namespace)

includes/
├── class-component.php       Base class: $name + $version
├── class-plugin.php          Orchestrator — wires all hooks
├── class-settings-core.php   Option read/write with type helpers
├── class-settings.php        Settings page, WordPress Settings API
├── class-script-blocker.php  PHP-side script capture + JS data injection
├── class-tracker-registry.php  Custom tracker registration (mwg_register_tracker)
├── class-user-controller.php   Per-user consent record CRUD
├── class-admin-hooks.php     Admin-only hook callbacks
├── class-public-hooks.php    Front-end hook callbacks
├── class-cf7-helper.php      Contact Form 7 integration
└── functions-admin-ui.php    Admin template rendering helpers

trackers/
├── tracker-facebook-pixel.php   Facebook Pixel delay-loading
├── tracker-google-analytics.php Google Analytics / gtag.js
└── tracker-microsoft-clarity.php  Microsoft Clarity

assets/
├── admin/                    Admin CSS + JS
└── public/                   Front-end CSS + JS (built by bin/build.js)
```

### Data flow

```
Page request
  → Script_Blocker::capture_blocked_script_handles()   (capture GA/FB/Clarity scripts)
  → Tracker PHP files inject stubs into <head>          (dataLayer, fbq stub, clarity queue)
  → wp_footer: Script_Blocker injects mgwcsData         (JSON config for JS)
  → mini-gdpr-cookie-popup.min.js loads                 (MiniGdprPopup class)
  → User interacts with popup
  → Accept: consentToScripts() → loadGoogleAnalytics() + loadFacebookPixel() etc.
  → Reject: rejectConsent() → no trackers loaded, AJAX records rejection
```

---

## Plugin Bootstrap

The plugin is initialised by `pp_mwg_plugin_run()` in `mini-wp-gdpr.php`:

```php
function pp_mwg_plugin_run() {
    $plugin = new Mini_Wp_Gdpr\Plugin( PP_MWG_PLUGIN_SLUG, PP_MWG_VERSION );
    $plugin->run();
}
add_action( 'plugins_loaded', 'pp_mwg_plugin_run' );
```

`Plugin::run()` registers all hooks. Components are instantiated lazily in `Plugin::init()` (fired on WordPress `init`) once the plugin is confirmed active via `is_mini_gdpr_enabled()`.

---

## Class Reference

### `Component` (base class)

All plugin classes extend `Component`. It provides:

| Property | Type | Description |
|---|---|---|
| `$name` | `string` | Plugin slug / text domain (`mini-wp-gdpr`) |
| `$version` | `string` | Plugin version (`2.0.0`) |

### `Plugin`

Central orchestrator. Access singleton instances via the getter methods:

```php
// These are NOT public API — use the mwg_* functions in functions.php instead.
// Listed here for plugin developers needing direct access in custom code.
$plugin = pp_mwg_get_plugin();   // returns the Plugin instance

$user_controller  = $plugin->get_user_controller();
$script_blocker   = $plugin->get_script_blocker();
$settings         = $plugin->get_settings_controller();
$cf7_helper       = $plugin->get_cf7_helper();
```

> **Prefer the public API functions** in `functions.php` over direct class access.
> The public functions are stable across versions; internal class APIs may change.

### `User_Controller`

Manages per-user consent records stored in user meta.

| Method | Description |
|---|---|
| `accept_gdpr_terms_now( $user_id = 0 )` | Record acceptance for user |
| `reject_gdpr_terms_now( $user_id = 0 )` | Record rejection for user |
| `has_user_accepted_gdpr( $user_id )` | Check acceptance |
| `has_user_rejected_gdpr( $user_id )` | Check rejection |
| `when_did_user_accept_gdpr( $user_id, $format )` | Get acceptance timestamp |
| `when_did_user_reject_gdpr( $user_id, $format )` | Get rejection timestamp |
| `clear_gdpr_accepted_status( $user_id = 0 )` | Delete all consent records |

User meta keys (defined in `constants.php`):

| Constant | Meta key | Description |
|---|---|---|
| `META_ACCEPTED_GDPR_WHEN_FIRST` | `_pwg_accepted_gdpr_when_first` | First acceptance date |
| `META_ACCEPTED_GDPR_WHEN_RECENT` | `_pwg_accepted_gdpr_when_recent` | Most recent acceptance date |
| `META_REJECTED_GDPR_WHEN` | `_pwg_rejected_gdpr_when` | Most recent rejection date |

### `Settings` / `Settings_Core`

`Settings_Core` provides typed option getters:

```php
// In Mini_Wp_Gdpr namespace internal use only
$settings = get_settings_controller();
$enabled  = $settings->get_bool( OPT_IS_COOKIE_CONSENT_POPUP_ENABLED );   // bool
$ga_id    = $settings->get_string( OPT_GA_TRACKING_CODE );                 // string
$duration = $settings->get_int( OPT_SCRIPT_CONSENT_DURATION );             // int
```

### `Script_Blocker`

Intercepts tracked scripts on the PHP side using `script_loader_tag` filter, then
injects the tracker configuration as `mgwcsData` JSON for the JavaScript layer.

See `enqueue_script_blocker_scripts()` and `capture_blocked_script_handles()` for
how scripts are blocked and how the JS data object is built.

### `Tracker_Registry`

Handles custom tracker registration. See [Registering Custom Trackers](#registering-custom-trackers).

---

## Constants Reference

All constants are defined in `constants.php` and use the `PP_MWG_` namespace or are in
the `Mini_Wp_Gdpr` namespace (for non-prefixed internal constants).

### Plugin constants (global)

| Constant | Value | Description |
|---|---|---|
| `PP_MWG_VERSION` | `2.0.0` | Plugin version |
| `PP_MWG_PLUGIN_SLUG` | `mini-wp-gdpr` | Plugin text domain / slug |
| `PP_MWG_PLUGIN_DIR` | *(path)* | Absolute path to plugin root |
| `PP_MWG_PLUGIN_URL` | *(url)* | URL to plugin root |

### Option key constants (`OPT_*`)

These are the `wp_options` keys. **Do not hardcode these strings** — always reference
the constant.

| Constant | Option key |
|---|---|
| `OPT_IS_COOKIE_CONSENT_POPUP_ENABLED` | `mwg_is_cookie_consent_popup_enabled` |
| `OPT_IS_GA_TRACKING_ENABLED` | `mwg_is_ga_enabled` |
| `OPT_GA_TRACKING_CODE` | `mwg_ga_tracking_code` |
| `OPT_GA_CONSENT_MODE_ENABLED` | `mwg_ga_consent_mode_enabled` |
| `OPT_IS_FB_PIXEL_TRACKING_ENABLED` | `mwg_is_fbpx_enabled` |
| `OPT_FB_PIXEL_ID` | `mwg_fbpx_id` |
| `OPT_IS_MS_CLARITY_TRACKING_ENABLED` | `mwg_is_ms_clarity_tracking_enabled` |
| `OPT_MS_CLARITY_ID` | `mwg_ms_clarity_id` |
| `OPT_SCRIPT_CONSENT_DURATION` | `mwg_consent_duration` |
| `OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE` | `mwg_cookie_and_tracker_consent_message` |
| `OPT_CONSENT_ACCEPT_TEXT` | `mwg_consent_accept_text` |
| `OPT_CONSENT_REJECT_TEXT` | `mwg_consent_reject_text` |
| `OPT_CONSENT_INFO_BTN_TEXT` | `mwg_consent_info_btn_text` |
| `OPT_CONSENT_BOX_POSITION` | `mwg_consent_box_position` |

---

## Registering Custom Trackers

Use the `mwg_register_tracker` filter to add trackers that will be loaded only after
the user gives consent. For detailed examples see
[`tracker-registration-api.md`](tracker-registration-api.md).

### Minimal example

```php
add_filter( 'mwg_register_tracker', function( array $trackers ) {
    $trackers['my-tracker'] = array(
        'name'   => 'My Analytics',
        'sdkUrl' => 'https://example.com/analytics.js',
    );
    return $trackers;
} );
```

**Tracker array keys:**

| Key | Type | Required | Description |
|---|---|---|---|
| `name` | `string` | ✅ | Display name shown in the Info overlay |
| `sdkUrl` | `string` | ✅ | Full URL of the tracker SDK to inject after consent |
| `async` | `bool` | ☐ | Load script with `async` attribute (default: `true`) |
| `onConsent` | `string` | ☐ | JS expression to run after SDK loads |

The SDK URL is loaded via `loadCustomTrackers()` in JavaScript after the user accepts.
The `onConsent` callback is evaluated once the script has loaded.

---

## Listening for Consent Events

### PHP action hooks

```php
// Fired when a logged-in user accepts consent (via AJAX or form).
add_action( 'mwg_consent_accepted', function( int $user_id ) {
    // e.g. record in CRM, unlock features, etc.
    update_user_meta( $user_id, 'my_plugin_consent_date', current_time( 'mysql' ) );
} );

// Fired when a logged-in user rejects consent (via AJAX).
add_action( 'mwg_consent_rejected', function( int $user_id ) {
    // e.g. log rejection, disable features
} );
```

Both hooks pass the WordPress user ID of the user making the decision.
They only fire for **logged-in users** — anonymous visitors store their decision
in `localStorage` only and no server-side hook fires.

### JavaScript events

The JavaScript layer does not dispatch DOM events, but you can hook into the
public JS API (see [JavaScript API](#javascript-api)) by overriding the
`window.mgwConsentGranted` or `window.mgwConsentRejected` callbacks if needed
in future custom builds. Currently the cleanest integration point is the PHP hooks above.

---

## Reading Consent State

### In PHP

Use the public functions from `functions.php`:

```php
// Check whether the current user has accepted.
if ( mwg_has_user_accepted_privacy_policy() ) {
    // Personalise content, unlock downloads, etc.
}

// Check a specific user.
if ( mwg_has_user_accepted_privacy_policy( $user_id ) ) {
    echo 'Accepted on: ' . mwg_when_did_user_accept_privacy_policy( $user_id );
}

// Render the acceptance checkbox form (for MyAccount / custom pages).
mwg_get_mini_accept_terms_form_for_current_user();
```

### Via AJAX / REST

The plugin does not currently expose a REST endpoint for consent state. If you
need to query consent via REST, add a custom endpoint and use the PHP helpers above:

```php
register_rest_route( 'my-plugin/v1', '/consent/(?P<user_id>\d+)', array(
    'methods'             => 'GET',
    'callback'            => function( \WP_REST_Request $request ) {
        $user_id = absint( $request->get_param( 'user_id' ) );
        return array(
            'accepted' => mwg_has_user_accepted_privacy_policy( $user_id ),
            'date'     => mwg_when_did_user_accept_privacy_policy( $user_id ),
        );
    },
    'permission_callback' => 'is_user_logged_in',
) );
```

---

## JavaScript API

The public JavaScript API is documented in [`hooks-and-filters.md`](hooks-and-filters.md#javascript-api).

Quick reference:

```javascript
// Programmatically reject scripts (e.g. from a custom preferences page).
window.mgwRejectScripts();

// Re-show the consent popup (e.g. from a settings panel).
window.mgwShowCookiePreferences();
```

The `mgwcsData` object is injected into the page footer by `Script_Blocker` and
contains all configuration needed by the JavaScript layer:

```javascript
// Available on every page where the plugin is active (logged in users have more fields).
console.log( window.mgwcsData );
// {
//   cn:          'mini_gdpr_cs',        // localStorage consent key
//   rcn:         'mini_gdpr_rc',        // localStorage rejection key
//   cd:          '365',                 // consent duration (days)
//   rjt:         '...',                 // reject button text
//   mre:         '...',                 // "manage preferences" text
//   trackers:    [...],                 // registered custom tracker definitions
//   gaId:        'G-XXXXXXXXXX',        // Google Analytics ID (if enabled)
//   fbpxId:      '...',                 // Facebook Pixel ID (if enabled)
//   clarityId:   '...',                 // Clarity project ID (if enabled)
//   // Logged-in users only:
//   ajaxUrl:     '...',
//   acceptAction:'acceptgdpr',
//   acceptNonce: '...',
//   rejectAction:'rejectgdpr',
//   rejectNonce: '...',
// }
```

---

## Settings API

Plugin settings are stored in `wp_options`. Access them using the `Settings_Core`
helpers within the plugin, or directly via `get_option()` in your own code:

```php
// Reading a setting outside the plugin:
$ga_enabled = filter_var( get_option( 'mwg_is_ga_enabled', false ), FILTER_VALIDATE_BOOLEAN );
$ga_id      = sanitize_text_field( get_option( 'mwg_ga_tracking_code', '' ) );
```

Always use the appropriate sanitisation function when reading options in your own code.

---

## Admin UI Extension Points

Currently the plugin does not expose admin UI hooks. The settings page is rendered
by `Settings::render_settings_page()` via direct template includes. If you need to
add a settings section, the recommended approach is to create a separate settings
page under your own plugin's admin menu.

A `mwg_settings_sections` filter may be added in a future release to allow
third-party sections — watch the changelog.

---

## Coding Standards

All code in this plugin follows WordPress Coding Standards (WPCS). Key rules:

- **No `declare(strict_types=1)`** — causes type errors at the WP boundary
- **Single-Entry Single-Exit (SESE)** — one `return` per function
- **Constants for all magic strings** — prefix defaults `DEF_`, options `OPT_`
- **Security everywhere** — sanitize input, escape output, verify nonces, check capabilities
- **Boolean options via `filter_var()`** — handles `'1'`, `'yes'`, `'on'`, etc.
- **Dates as human-readable strings** — `Y-m-d H:i:s T` format, never Unix timestamps

Run `bin/check.sh` before every commit.

---

*See also:*
- [`hooks-and-filters.md`](hooks-and-filters.md) — complete hook/filter/API reference
- [`tracker-registration-api.md`](tracker-registration-api.md) — detailed custom tracker guide
- [`migration-guide.md`](migration-guide.md) — upgrading from v1.x

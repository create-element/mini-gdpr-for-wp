# Hooks, Filters & Public API Reference

**Plugin:** Mini WP GDPR v2.0.0  
**Last Updated:** 18 February 2026  
**Namespace:** `Mini_Wp_Gdpr`  
**Hook prefix:** `mwg_` (public API), `pp_mwg_` (internal/integration)

All hooks use the `mwg_` prefix by convention. WordPress Coding Standards requires prefixes of 4+ characters; we use `mwg_` as it is the established identity of this plugin's public API. PHPCS suppressions are in place where required.

---

## PHP Public Functions

These global functions form the public-facing developer API. They are intentionally in the global namespace so themes and plugins can call them without importing the `Mini_Wp_Gdpr` namespace.

### `mwg_has_user_accepted_privacy_policy( int $user_id = 0 ) : bool`

Returns whether the given user has accepted the privacy policy.

```php
// Check current user
if ( mwg_has_user_accepted_privacy_policy() ) {
    // User has accepted
}

// Check a specific user
if ( mwg_has_user_accepted_privacy_policy( 42 ) ) {
    // User 42 has accepted
}
```

**Parameters:**
- `$user_id` *(int)* — WordPress user ID. Defaults to the current user (`get_current_user_id()`).

**Returns:** `bool` — `true` if the user has accepted.

---

### `mwg_when_did_user_accept_privacy_policy( int $user_id = 0, string $format = '' ) : string|null`

Returns the date/time when the given user accepted the privacy policy.

```php
// Get accept date for current user (site date format)
$date = mwg_when_did_user_accept_privacy_policy();

// Custom format for a specific user
$date = mwg_when_did_user_accept_privacy_policy( 42, 'Y-m-d' );
```

**Parameters:**
- `$user_id` *(int)* — WordPress user ID. Defaults to current user.
- `$format` *(string)* — PHP date format string. Defaults to the site's `date_format` option.

**Returns:** `string|null` — Formatted date, or `null` if the user has not accepted.

---

### `mwg_get_mini_accept_terms_form_for_current_user() : void`

Renders the GDPR acceptance checkbox form for the current user. Outputs the `public-templates/mini-accept-form.php` template directly.

```php
// In a theme template or shortcode
mwg_get_mini_accept_terms_form_for_current_user();
```

---

## JavaScript Public API

These functions are available on the `window` global after `mini-gdpr-cookie-popup.js` loads.

### `window.mgwRejectScripts()`

Programmatically reject tracking consent. Stores rejection in localStorage/cookie and prevents any tracking scripts from loading.

```js
// Reject consent programmatically (e.g. via a custom button)
document.querySelector('#my-reject-btn').addEventListener('click', function () {
    window.mgwRejectScripts();
});
```

---

### `window.mgwShowCookiePreferences()`

Shows the consent popup again, allowing the user to change their decision. Clears the current stored decision (accept or reject) and re-displays the popup.

```js
// Show cookie preferences (e.g. from a footer link)
document.querySelector('#cookie-settings-link').addEventListener('click', function (e) {
    e.preventDefault();
    window.mgwShowCookiePreferences();
});
```

---

## Action Hooks

### `mwg_consent_accepted`

Fires when a logged-in user accepts cookie/tracking consent via the AJAX endpoint.

**Does NOT fire** for anonymous users (no user to record against).

```php
add_action( 'mwg_consent_accepted', function ( int $user_id ) {
    // Record consent in a custom table
    my_plugin_record_gdpr_consent( $user_id, 'accepted' );
}, 10, 1 );
```

**Parameters:**
- `$user_id` *(int)* — The ID of the user who accepted.

---

### `mwg_consent_rejected`

Fires when a logged-in user rejects cookie/tracking consent via the AJAX endpoint.

**Does NOT fire** for anonymous users.

```php
add_action( 'mwg_consent_rejected', function ( int $user_id ) {
    // Record rejection in a custom table
    my_plugin_record_gdpr_consent( $user_id, 'rejected' );
}, 10, 1 );
```

**Parameters:**
- `$user_id` *(int)* — The ID of the user who rejected.

---

### `mwg_inject_tracker_{handle}`

Fires to inject a specific tracker's scripts after consent. Replace `{handle}` with the sanitised slug of the tracker (e.g. `mwg_inject_tracker_my-custom-tracker`).

Use this to enqueue scripts or output tracker-specific markup when the tracker is enabled and its injection point fires.

```php
add_action( 'mwg_inject_tracker_my-custom-tracker', function () {
    wp_enqueue_script(
        'my-custom-tracker',
        'https://example.com/tracker.js',
        [],
        '1.0.0',
        true
    );
});
```

---

### `mwg_init_blockable_scripts`

Fires inside `Script_Blocker::get_blockable_scripts()` just before the blockable-handles list is built. Allows late-binding of tracker filter registrations.

This hook is used internally by `Tracker_Registry`. You rarely need to hook it directly — use `mwg_register_tracker` instead.

---

## Filter Hooks

### `mwg_register_tracker`

**The primary API for adding custom third-party trackers.**

Register one or more custom trackers. Each tracker is automatically wired into the Script_Blocker pattern-match system and the consent popup's JavaScript delay-loading.

```php
add_filter( 'mwg_register_tracker', function ( array $trackers ) : array {
    $trackers['hotjar'] = [
        'handle'      => 'hotjar-analytics',     // Required — WP script handle
        'description' => 'Hotjar',               // Required — shown in info overlay
        'sdk_url'     => 'https://static.hotjar.com/c/hotjar-12345.js?sv=6', // Required
        'pattern'     => '/hotjar-[0-9]+\.js/',  // Optional — for server-side blocking
        'field'       => 'src',                  // Optional — 'src' (default) or 'outerhtml'
        'can_defer'   => true,                   // Optional — suppress pre-consent (default false)
    ];
    return $trackers;
} );
```

**Tracker definition keys:**

| Key | Type | Required | Description |
|-----|------|----------|-------------|
| `handle` | string | ✅ | WordPress script handle to register as blockable. Use lowercase with hyphens. |
| `description` | string | ✅ | Human-readable name shown in the "Cookie Information" info overlay. |
| `sdk_url` | string | ✅ | Full URL of the tracker SDK. Loaded by `loadCustomTrackers()` after consent. |
| `pattern` | string | ❌ | Regex pattern (with delimiters) for server-side script blocking. |
| `field` | string | ❌ | Which field to match the pattern against: `'src'` (default) or `'outerhtml'`. |
| `can_defer` | bool | ❌ | If `true`, the script tag is suppressed pre-consent. Default `false`. |

See also: [`dev-notes/tracker-registration-api.md`](tracker-registration-api.md) for a full walkthrough.

---

### `mwg_blockable_script_handles`

Modify the list of WordPress script handles that are treated as blockable (subject to consent-based loading).

```php
add_filter( 'mwg_blockable_script_handles', function ( array $handles ) : array {
    $handles[] = 'my-legacy-tracker-handle';
    return $handles;
} );
```

**Parameters:**
- `$handles` *(array)* — Existing list of blockable handles.

**Returns:** *(array)* — Updated handles array.

---

### `mwg_injectable_tracker_metas`

Modify the full tracker metadata array. Allows bulk changes to tracker definitions before the Script_Blocker builds its pattern-match list.

```php
add_filter( 'mwg_injectable_tracker_metas', function ( array $metas ) : array {
    // Modify or extend tracker meta definitions
    $metas['my-custom-tracker'] = [
        'pattern'     => '/example\.com\/tracker\.js/',
        'field'       => 'src',
        'description' => 'My Custom Tracker',
        'can-defer'   => true,
    ];
    return $metas;
} );
```

---

### `mwg_tracker_{handle}`

Define or override the metadata for a specific tracker. `{handle}` is the sanitised tracker slug (e.g. `mwg_tracker_my-custom-tracker`).

Prefer `mwg_register_tracker` for new trackers — use this filter only to override existing built-in tracker definitions.

```php
// Override the Google Analytics tracker pattern
add_filter( 'mwg_tracker_mgw-google-analytics', function ( array $defaults ) : array {
    $defaults['can-defer'] = false; // Never defer GA
    return $defaults;
} );
```

**Parameters:**
- `$defaults` *(array)* — Default definition with keys: `pattern`, `field`, `description`, `can-defer`.

**Returns:** *(array)* — Updated tracker definition.

---

### `mwg_is_tracker_enabled`

Control whether a specific tracker is enabled for the current request. Return `false` to prevent the tracker from injecting, regardless of user consent.

```php
// Disable Facebook Pixel on the homepage
add_filter( 'mwg_is_tracker_enabled', function ( bool $enabled, string $handle ) : bool {
    if ( is_front_page() && 'mgw-facebook-pixel' === $handle ) {
        return false;
    }
    return $enabled;
}, 10, 2 );
```

**Parameters:**
- `$enabled` *(bool)* — Whether the tracker is currently enabled.
- `$handle` *(string)* — The tracker handle being checked.

**Returns:** *(bool)*

---

### `mwg_dont_track_roles`

Customize which WordPress user roles are excluded from tracking. Users in any of the listed roles will never see the consent popup and trackers will never fire for them.

```php
add_filter( 'mwg_dont_track_roles', function ( array $roles ) : array {
    $roles[] = 'editor';     // Also exclude editors
    $roles[] = 'shop_manager'; // And shop managers
    return $roles;
} );
```

**Default value:** `['administrator']` (controlled by `DEFAULT_DONT_TRACK_ADMIN_ROLES` constant).

**Parameters:**
- `$roles` *(array)* — Role slugs to exclude from tracking.

**Returns:** *(array)*

---

### `mwg_additional_blocked_scripts`

Add extra inline script patterns to block server-side, beyond the registered tracker handles.

```php
add_filter( 'mwg_additional_blocked_scripts', function ( array $patterns ) : array {
    $patterns[] = '/my-legacy-tracker\.js/';
    return $patterns;
} );
```

**Parameters:**
- `$patterns` *(array)* — Array of regex pattern strings (with delimiters).

**Returns:** *(array)*

---

### `mwg_consent_box_classes`

Customize the CSS classes applied to the consent popup container (`#mgwcsCntr`).

```php
add_filter( 'mwg_consent_box_classes', function ( array $classes ) : array {
    $classes[] = 'my-custom-theme-popup';
    return $classes;
} );
```

**Parameters:**
- `$classes` *(array)* — Current array of CSS class names.

**Returns:** *(array)*

---

### `pp_mwg_myaccount_priority`

Change the hook priority at which the GDPR acceptance form is injected into the WooCommerce MyAccount page.

```php
add_filter( 'pp_mwg_myaccount_priority', function ( int $priority ) : int {
    return 30; // Inject later (after WC default content)
} );
```

**Default:** `DEFAULT_MYACCOUNT_INJECT_PRIORITY` (see `constants.php`)

---

### `pp_mwg_enable_gdpr_registration_validation`

Enable or disable GDPR consent validation on the WooCommerce registration form. When `true`, registration will fail if the user does not tick the consent checkbox.

```php
// Disable GDPR validation for B2B registrations
add_filter( 'pp_mwg_enable_gdpr_registration_validation', function ( bool $enabled ) : bool {
    if ( is_page( 'b2b-registration' ) ) {
        return false;
    }
    return $enabled;
} );
```

---

### `pp_mwg_your_email_tag_name`

Customize the Contact Form 7 field tag name used to identify the email field when installing consent checkboxes.

```php
add_filter( 'pp_mwg_your_email_tag_name', function ( string $tag ) : string {
    return 'email-address'; // Custom email field name
} );
```

**Default:** `CF7_YOUR_EMAIL_TAG_NAME` (see `constants.php`)

---

## Quick Reference

| Hook | Type | Since | Description |
|------|------|-------|-------------|
| `mwg_consent_accepted` | Action | 2.0.0 | User accepted consent (logged-in) |
| `mwg_consent_rejected` | Action | 2.0.0 | User rejected consent (logged-in) |
| `mwg_inject_tracker_{handle}` | Action | 1.0.0 | Inject tracker scripts after consent |
| `mwg_init_blockable_scripts` | Action | 2.0.0 | Before blockable scripts list is built |
| `mwg_register_tracker` | Filter | 2.0.0 | Register custom third-party trackers |
| `mwg_blockable_script_handles` | Filter | 1.0.0 | Modify blockable script handles list |
| `mwg_injectable_tracker_metas` | Filter | 1.0.0 | Modify tracker metadata array |
| `mwg_tracker_{handle}` | Filter | 1.0.0 | Define metadata for a specific tracker |
| `mwg_is_tracker_enabled` | Filter | 1.0.0 | Control per-tracker per-request enabling |
| `mwg_dont_track_roles` | Filter | 1.0.0 | User roles excluded from tracking |
| `mwg_additional_blocked_scripts` | Filter | 1.0.0 | Extra server-side block patterns |
| `mwg_consent_box_classes` | Filter | 2.0.0 | CSS classes on consent popup container |
| `pp_mwg_myaccount_priority` | Filter | 1.0.0 | WC MyAccount injection priority |
| `pp_mwg_enable_gdpr_registration_validation` | Filter | 1.0.0 | Toggle WC registration validation |
| `pp_mwg_your_email_tag_name` | Filter | 1.0.0 | CF7 email field tag name override |

---

## See Also

- [`tracker-registration-api.md`](tracker-registration-api.md) — Full tracker registration walkthrough
- [`tracker-delay-loading.md`](tracker-delay-loading.md) — How delay-loading works for each built-in tracker
- [`consent-api-research.md`](consent-api-research.md) — Google Consent Mode v2, FB Pixel, Clarity, IAB TCF research
- [`patterns/`](patterns/) — Implementation patterns for settings, admin tabs, AJAX, etc.

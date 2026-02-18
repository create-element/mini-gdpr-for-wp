# Mini WP GDPR — Upgrade FAQ

**Covers:** Upgrading from v1.4.3 to v2.0.0

For a full change log see [CHANGELOG.md](../CHANGELOG.md). For a structured migration guide see [migration-guide.md](migration-guide.md).

---

## General

### Will my existing settings survive the upgrade?

**Yes.** All option keys are preserved exactly as they were in v1.x. No data migration or manual re-configuration is required. Install v2.0.0 over your existing installation and all settings carry over automatically.

### Will I lose any consent records stored for my users?

**No.** User consent timestamps are stored in `wp_usermeta` under the same keys that v1.x used (`_pwg_accepted_gdpr_when`, `_pwg_accepted_gdpr_when_first`). These are untouched during the upgrade. The new rejection key (`_pwg_rejected_gdpr_when`) is simply absent for existing users until they explicitly click "Reject".

### Is a database migration script needed?

**No.** v2.0.0 adds two new `wp_options` keys (for rejection button text and info button text) but reads default values when they are absent. Existing keys are neither renamed nor removed. The upgrade is fully transparent for end users.

### Does v2.0.0 work on the same PHP and WordPress versions as v1.x?

v2.0.0 requires **PHP 7.4+** and **WordPress 6.4+**. If your site is running an older environment you should update PHP/WordPress before upgrading the plugin.

---

## The Reject Button

### Why is there a "Reject" button now?

GDPR requires that users can refuse tracking with the same ease as they can accept it. A consent popup that only offers "Accept" or "More info" does not meet that standard. The v2.0.0 popup now shows **Reject · Info · Accept** buttons side by side.

### What happens when a user clicks Reject?

- The rejection decision is stored in `localStorage` (with a `document.cookie` fallback).
- No tracking scripts (Google Analytics, Facebook Pixel, Microsoft Clarity, or custom trackers) are loaded.
- The popup fades out and a floating 🍪 "Manage preferences" button appears so users can change their mind.
- For logged-in users a server-side record is also written to `wp_usermeta` via AJAX.

### Can I customise the button labels?

Yes. Go to **Settings → Mini WP GDPR → Cookie Consent** and find the *Accept button text*, *Reject button text*, and *Info button text* fields.

### Can I hide the Reject button?

The Reject button is a core GDPR compliance feature and cannot be hidden from the settings panel. If you need to remove it for a specific reason, unhook the `mwg_consent_rejected` action or filter the popup via the `mwg_popup_classes` filter and override the CSS to hide `#mgwcsCntr .mgw-reject`.

---

## JavaScript API Changes

### My code called `ppConsentToScripts()`. It's broken after the upgrade.

The old `ppConsentToScripts()` global has been renamed. Use the new public API:

```js
// v1.x (old)
ppConsentToScripts();

// v2.0.0 (new)
window.mgwAcceptCookies();   // programmatically accept
window.mgwRejectScripts();   // programmatically reject
window.mgwShowCookiePreferences(); // re-open the preferences UI
```

These are available immediately after `mini-gdpr-cookie-popup.min.js` loads.

### I had JavaScript that read `localStorage.mini_gdpr_consent`. It's empty now.

The storage key changed between versions. In v2.0.0 the keys are:

- **Acceptance:** `localStorage[mgwcsData.cn]`  (where `cn` is the consent cookie name option)
- **Rejection:** `localStorage[mgwcsData.rcn]` (where `rcn` is the rejection cookie name)

Both keys store a human-readable date string. Read them via the public API functions rather than directly from storage where possible:

```js
// Check whether the user has made any decision.
// (No public read API — read the storage key directly if needed.)
const cn  = window.mgwcsData && window.mgwcsData.cn;
const rcn = window.mgwcsData && window.mgwcsData.rcn;
const hasAccepted = cn  && !!localStorage.getItem( cn );
const hasRejected = rcn && !!localStorage.getItem( rcn );
```

### jQuery is no longer a dependency of the plugin scripts. Will that break anything?

Only if you were relying on the plugin to enqueue jQuery as a side-effect. This is unlikely. If you see jQuery-related errors in the browser console after upgrading, verify that your own theme or plugin enqueues jQuery explicitly via `wp_enqueue_script( 'jquery' )`.

---

## PHP API Changes

### The `pp_core_is_mini_gdpr_enabled()` function I used no longer exists.

Functions from `pp-core.php` were all internal to that library and are not part of the Mini WP GDPR public API. Replace with the equivalents:

| Old (pp-core internal) | Replacement |
|---|---|
| `pp_core_is_mini_gdpr_enabled()` | `is_mini_gdpr_enabled()` (plugin internal, or check `mwg_has_user_accepted_privacy_policy()`) |
| `pp_core_get_date_time_now_h()` | Not public API — use PHP's `date_i18n()` or `current_time()` instead |

For the full public PHP API see [hooks-and-filters.md](hooks-and-filters.md).

### I have a custom plugin that extends the `Component` base class from pp-core. It broke.

The `Component` class is now part of Mini WP GDPR itself (`includes/class-component.php`, namespace `Mini_Wp_Gdpr`). If you were extending pp-core's `PP_Framework\Component`, you will need to update:

```php
// v1.x (old — extending pp-core)
class My_Custom_Class extends \PP_Framework\Component { ... }

// v2.0.0 (new — extending plugin-native class)
use Mini_Wp_Gdpr\Component;
class My_Custom_Class extends Component { ... }
```

Note that `pp-core.php` has been archived and is **no longer loaded** by the plugin.

---

## Tracker Integrations

### Google Analytics stopped loading after upgrading.

v2.0.0 implements **Google Consent Mode v2** with consent defaulting to `denied`. This means the `gtag.js` SDK is **not** injected until the user clicks Accept (that is the intended behaviour). If you are seeing GA not fire even after acceptance:

1. Check the browser console for JavaScript errors.
2. Verify your GA Tracking ID is correct in **Settings → Mini WP GDPR → Trackers → Google Analytics**.
3. Make sure "Enable GA Consent Mode v2" is checked if you want the `consent,update` signal sent.
4. Hard-refresh (Ctrl+Shift+R) to clear any cached decisions.

### Facebook Pixel isn't firing after acceptance.

The Pixel SDK is delay-loaded. After acceptance, `loadFacebookPixel()` is called which: grants the FB Consent API, loads `fbevents.js` dynamically, then calls `fbq('init', ...)` and `fbq('track', 'PageView')`. If the Pixel is still not firing:

1. Confirm your Pixel ID is entered in **Settings → Mini WP GDPR → Trackers → Facebook Pixel**.
2. Confirm the user role is not excluded from tracking.
3. Use the Facebook Pixel Helper browser extension to verify the `PageView` event fires.

### The cookie popup now says "Reject · Info · Accept" but my old CSS only styled two buttons.

The popup layout changed from two buttons (`#mgwcsCntr .mgw-info` and `#mgwcsCntr .mgw-accept`) to three. Update your CSS to also handle `.mgw-reject`. Full button selectors:

```css
/* Accept button */
#mgwcsCntr .mgw-accept { }

/* Reject button (new in v2.0.0) */
#mgwcsCntr .mgw-reject { }

/* "More info" / overlay button */
#mgwcsCntr .mgw-info { }
```

---

## WooCommerce & Contact Form 7

### My WooCommerce checkout GDPR checkbox disappeared.

Check that **Settings → Mini WP GDPR → WooCommerce → Enable WooCommerce registration consent** is enabled. v2.0.0 changed nothing about the WooCommerce integration logic; if the checkbox was showing before it should continue showing.

### The Contact Form 7 consent checkbox was removed from my form.

Re-open **Settings → Mini WP GDPR → Contact Form 7** and click *Install consent box* for the relevant form. The v2.0.0 installer is idempotent — it will not duplicate the checkbox if it is already present.

---

## Rollback

### I need to roll back to v1.4.3. Will I lose data?

No data is lost. v2.0.0 writes only the two new rejection meta keys; v1.4.3 will simply ignore them. Roll back by re-installing the old plugin files. All existing option keys and user meta remain intact.

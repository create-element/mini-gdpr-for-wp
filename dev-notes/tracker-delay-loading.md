# Tracker Delay-Loading Architecture

**Version:** 2.0.0  
**Date:** 2026-02-17  
**Relates to:** Milestone 6 — Advanced Tracker Delay-Loading

---

## Overview

Mini WP GDPR uses a **stub + deferred load** pattern to ensure third-party tracker
scripts never load until the user explicitly accepts tracking. This document explains
how the pattern works, how each tracker implements it, and how to add a new tracker.

---

## The Core Pattern

Every GDPR-compliant tracker integration has three parts:

### 1. PHP Stub (inline script in `<head>`)

A small, harmless inline script that creates the tracker's global API as a
queue-backed function. This runs on every page load, before the user makes a
consent decision.

- **Sends no data to third parties** — it only creates a local JavaScript queue
- **Intercepts tracker API calls** (e.g. `fbq('track', ...)`, `clarity(...)`) made by
  theme or plugin code before consent, storing them in the queue
- Registered via `wp_add_inline_script()` with `can-defer: false` (never suppressed
  by the script blocker — the stub is safe to output unconditionally)

### 2. PHP Tracker File

The tracker file (`trackers/tracker-*.php`) wires up three filters/actions:

| Hook | Purpose |
|---|---|
| `mwg_blockable_script_handles` | Registers the handle so Script_Blocker tracks it |
| `mwg_tracker_{handle}` | Returns pattern, field, description, and `can-defer` flag |
| `mwg_inject_tracker_{handle}` | Outputs the stub (inline script, no external URL) |

### 3. JavaScript Deferred Loader

A method in `MiniGdprPopup` (e.g. `loadFacebookPixel()`, `loadMicrosoftClarity()`)
that dynamically injects the real tracker script after the user consents.

Called from two places in `MiniGdprPopup.init()` / `MiniGdprPopup.consentToScripts()`:
- **On consent** — immediately after the user clicks Accept
- **On returning visit** — when `hasConsented()` is true (user already decided)

The tracker ID is passed to JavaScript via `mgwcsData` (set in `Script_Blocker::capture_blocked_script_handles()`).

---

## Tracker Implementations

### Facebook Pixel

**Status:** ✅ Implemented (Milestone 4/5)

**Stub:** Creates `window.fbq` as a queue-backed function and calls `fbq('init', ID)`
+ `fbq('track', 'PageView')`. These calls are queued locally.

**Deferred loader:** `loadFacebookPixel()` appends:
```html
<script async src="https://connect.facebook.net/en_US/fbevents.js"></script>
```

**Replay:** The Facebook Pixel SDK (`fbevents.js`) automatically processes
`window.fbq.queue` on load, replaying `init` and `track` calls.

**Why the pixel ID is in the stub:** `fbq('init', 'PIXEL_ID')` must be called before
PageView so the SDK knows which pixel account to send data to. The stub queues this call;
the SDK replays it when `fbevents.js` loads.

**GDPR compliance:** `fbevents.js` never loads before consent. The stub creates only
local data structures — no network requests to Meta.

---

### Google Analytics

**Status:** ✅ Implemented (Milestone 6, Phase 6.2)

Google Analytics follows the same **stub + deferred load** pattern as Facebook Pixel
and Microsoft Clarity, ensuring `gtag.js` never loads before consent regardless of
whether the "block scripts until consent" option is enabled.

**Stub (wp_head, priority 1):** Initialises `window.dataLayer` and defines the `gtag()`
function unconditionally when GA is enabled. When Consent Mode v2 is also enabled,
adds `gtag('consent', 'default', {...denied})` to the stub.

**Config stub (mwg_inject_tracker_):** An empty-src registered inline script that queues
`gtag('js', new Date())` and `gtag('config', 'G-XXXX')` in `dataLayer`. These calls
are stored locally — `gtag.js` is not loaded.

**Deferred loader:** `loadGoogleAnalytics()` dynamically appends:
```html
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXX"></script>
```
When `gtag.js` loads, it processes the full `dataLayer` queue in order and initialises
GA tracking. With Consent Mode v2 enabled, the consent update signal (fired in
`consentToScripts()` before `loadGoogleAnalytics()`) ensures tracking starts in the
fully-granted state.

**Replay:** `gtag.js` processes `window.dataLayer` on load, replaying all queued calls
including `consent,default`, `js`, `config`, and `consent,update,{granted}`.

**GDPR compliance:** `gtag.js` never loads before consent. The stubs create only local
`dataLayer` entries — no network requests to Google until the user explicitly accepts.

**See:** `trackers/tracker-google-analytics.php`, `dev-notes/consent-api-research.md`

---

### Microsoft Clarity

**Status:** ✅ Implemented (Milestone 6, Phase 6.3)

**Previous (v1.x) behaviour:** The full Clarity snippet (which dynamically loaded
`clarity.ms/tag/<ID>`) was output unconditionally on every page. This meant Clarity
loaded before consent — a GDPR violation.

**New (v2.0) behaviour:**

**Preconnect hint (wp_head, priority 1):** When Clarity is enabled, a
`<link rel="preconnect" href="https://www.clarity.ms">` hint is output in `<head>`.
This pre-establishes the DNS/TCP/TLS connection before the user consents, reducing
latency when `loadMicrosoftClarity()` fires after the user accepts. The preconnect
itself transmits no user data to Microsoft.

**Stub (mwg_inject_tracker_):** An empty-src registered inline script that creates
`window.clarity` as a queue-backed function:
```js
window.clarity = window.clarity || function() {
    (window.clarity.q = window.clarity.q || []).push(arguments);
};
```

**Deferred loader:** `loadMicrosoftClarity()` appends:
```html
<script async src="https://www.clarity.ms/tag/<ID>"></script>
```

**Replay:** The Clarity SDK discovers `window.clarity.q` on load and processes any
queued calls.

**No consent API:** Unlike Google Analytics (Consent Mode v2) and Facebook Pixel
(`fbq('consent','grant')`), Microsoft Clarity does not have a consent API. The
GDPR-compliant approach is simply not to load Clarity until consent is given — which
is exactly what this implementation enforces. No pre-consent signals need to be queued.

**ID validation:** The PHP tracker validates the Clarity project ID format
(`/^[a-zA-Z0-9_-]{1,32}$/`) and logs errors for missing or malformed IDs, matching
the validation pattern used by the Google Analytics tracker.

**GDPR compliance:** `clarity.ms` never loads before consent. The stub creates only
a local queue — no network requests to Microsoft until the user explicitly accepts.

---

## Data Flow

```
Page load
  │
  ├─ PHP outputs preconnect hints + tracker stubs in <head>
  │   ├─ <link rel="preconnect" href="https://www.googletagmanager.com">
  │   ├─ <link rel="preconnect" href="https://www.clarity.ms">
  │   ├─ dataLayer + gtag() stub + consent.default=denied (GA, when Consent Mode enabled)
  │   ├─ fbq stub + fbq('consent','revoke') + fbq('init') + fbq('track','PageView')
  │   └─ window.clarity queue stub
  │   └─ No external requests. Queued API calls stored locally.
  │
  ├─ Script_Blocker: detects stub patterns, passes IDs to mgwcsData
  │
  └─ Consent popup shown to user
        │
        ├─ User ACCEPTS
        │   ├─ Store consent timestamp (localStorage / cookie)
        │   ├─ Google Consent Mode update (gtag consent granted)
        │   ├─ insertBlockedScripts() → loads can-defer scripts
        │   ├─ loadGoogleAnalytics() → loads gtag.js (replays dataLayer queue)
        │   ├─ loadFacebookPixel() → fbq('consent','grant') + loads fbevents.js
        │   └─ loadMicrosoftClarity() → loads clarity.ms/tag/<ID> (replays clarity.q)
        │
        └─ User REJECTS
            └─ Store rejection timestamp. No tracker scripts loaded. Ever.
```

---

## Adding a New Tracker

To add a GDPR-compliant delay-loading tracker:

### 1. Add constants

In `constants.php`:
```php
const MY_TRACKER_SCRIPT_HANDLE           = 'mgw-my-tracker';
const OPT_MY_TRACKER_ID                  = 'mwg_my_tracker_id';
const OPT_IS_MY_TRACKER_TRACKING_ENABLED = 'mwg_is_my_tracker_enabled';
```

### 2. Create `trackers/tracker-my-tracker.php`

Follow the pattern in `tracker-microsoft-clarity.php`:
- `mwg_blockable_script_handles` filter — add the handle
- `mwg_tracker_{handle}` filter — return pattern, field, description, `can-defer: false`
- `mwg_inject_tracker_{handle}` action — output the queue stub via `wp_add_inline_script()`

### 3. Add deferred loader to `assets/mini-gdpr-cookie-popup.js`

```js
loadMyTracker() {
    if ( ! this.data.myTrackerId ) {
        return;
    }
    const script = document.createElement( 'script' );
    script.src   = 'https://example.com/tracker.js?id=' + this.data.myTrackerId;
    script.async = true;
    document.head.appendChild( script );
}
```

Call `this.loadMyTracker()` from both `consentToScripts()` and `init()` (when `hasConsented()` is true).

### 4. Pass the tracker ID in `class-script-blocker.php`

In `capture_blocked_script_handles()`, after the existing tracker ID blocks:
```php
if ( $settings->get_bool( OPT_IS_MY_TRACKER_TRACKING_ENABLED ) && ! $this->are_trackers_blocked_by_role ) {
    $raw_id = $settings->get_string( OPT_MY_TRACKER_ID );
    if ( ! empty( $raw_id ) ) {
        $localize_data['myTrackerId'] = esc_js( sanitize_text_field( $raw_id ) );
    }
}
```

### 5. Rebuild minified assets

```bash
npm run build
```

---

## Notes & Limitations

### noScript pixels

The Facebook Pixel and some other trackers offer a `<noscript>` fallback that fires
a server-side impression for users with JavaScript disabled. **This is incompatible
with GDPR consent requirements**: a `<noscript>` tag cannot be made conditional on
a JavaScript consent decision. Mini WP GDPR does not implement noscript pixel fallbacks.

### Script queueing before stub loads

Theme or plugin code that calls `fbq()` or `clarity()` via inline scripts in `<head>`
before the stub runs will see `window.fbq`/`window.clarity` as undefined. To avoid
this, load the stub at an early priority (we use `false` / in-head loading) and ensure
your theme calls these APIs after `wp_head()`.

### Custom events on rejection

If the user changes their decision from Accept → Reject (via the manage preferences
button), tracker scripts that are already loaded for that session remain active.
The rejection is stored for future page loads, where no tracker scripts will be loaded.
This is an accepted limitation — revoking already-loaded third-party SDKs from a
running page is not reliably possible without a full page reload.

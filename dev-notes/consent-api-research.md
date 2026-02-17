# Consent API Integration Research

**Date:** 17 February 2026  
**Sprint:** M5 Phase 5.3  
**Author:** Zee (AI coding assistant)

---

## Overview

This document covers the research, feasibility evaluation, and implementation decisions for integrating consent signalling APIs into Mini WP GDPR v2.0.0. The "browser Consent API" is not a single unified standard — it is several overlapping mechanisms operated by different platforms and standards bodies.

---

## APIs Evaluated

### 1. Google Consent Mode v2

**What it is:**  
Google Consent Mode v2 is a consent signalling framework built into the GA4/gtag.js ecosystem. It allows a site to send consent state (granted/denied) to Google before or after the user decides, so Google can model and report on data in a GDPR-compliant way.

**Consent categories:**
| Parameter | Meaning |
|---|---|
| `analytics_storage` | Analytics cookies (GA4 session/user data) |
| `ad_storage` | Advertising cookies (remarketing, conversion) |
| `ad_user_data` | Sending user data to Google for advertising |
| `ad_personalization` | Personalised advertising |

**How it works:**
1. Before user decides: set all categories to `'denied'` with `gtag('consent', 'default', {...})`. A `wait_for_update` window (milliseconds) tells GA to hold events and wait for a consent update before sending them.
2. After user accepts: call `gtag('consent', 'update', {analytics_storage: 'granted', ...})`. GA upgrades the current session to full tracking.
3. If user rejects (or never decides): the default `'denied'` state persists. GA sends only cookieless pings so Google can model aggregate trends.

**Browser compatibility:**  
Fully supported in all major browsers (Chrome, Firefox, Safari, Edge). No browser API dependency — runs entirely as JavaScript.

**Feasibility:** ✅ **High — implemented in this sprint.**

**Key requirement:**  
The `gtag('consent', 'default', {...})` call must appear in the page `<head>` **before** the `gtag.js` script loads. Our current architecture blocks GA until consent, so the flow is:
1. `wp_head` (priority 1): output `dataLayer` init + `gtag()` stub + consent defaults → queued in `dataLayer`.
2. User accepts: `mini-gdpr-cookie-popup.js` calls `gtag('consent', 'update', {...})` → queued in `dataLayer`.
3. `insertBlockedScripts()` loads `gtag.js` → GA reads the queued `dataLayer` and starts in granted state.

**Implementation status:** ✅ **Complete (M5 Phase 5.3)**
- `OPT_GA_CONSENT_MODE_ENABLED` option added to constants.php and settings.
- Admin toggle added to Settings → Trackers → Google Analytics.
- `tracker-google-analytics.php`: `wp_head` hook at priority 1 outputs default signals.
- `mini-gdpr-cookie-popup.js`: `consentToScripts()` fires `gtag('consent', 'update', {...})` before `insertBlockedScripts()`.
- Guard: `typeof gtag === 'function'` — no-op when Consent Mode is disabled.

**Recommendation:** Enable for all GA4 users, especially those with EU/UK traffic. Required by Google for certain GA4 attribution features. Not needed for users who have GA entirely disabled.

---

### 2. Facebook Pixel Consent API

**What it is:**  
Facebook's consent signalling mechanism for the FB Pixel SDK. Calling `fbq('consent', 'revoke')` before the pixel initialises prevents it from storing cookies or sending any data. Calling `fbq('consent', 'grant')` re-enables full tracking.

**How it works:**
1. Output FB Pixel code immediately (in `<head>`) but call `fbq('consent', 'revoke')` before `fbq('init', ...)`.
2. After user accepts: call `fbq('consent', 'grant')`.
3. The pixel operates in a "limited" mode until consent is granted — no cookies, no event tracking.

**Browser compatibility:** All major browsers. Pure JavaScript, no browser API dependency.

**Feasibility:** ✅ **High — but requires architectural change (deferred to M6).**

**Why deferred:**  
Currently, FB Pixel is registered as `'can-defer': false` in the tracker metadata. This means the current architecture blocks the entire pixel (including the `<script>` tag) until consent. Implementing FB Pixel Consent Mode requires changing this to load the pixel immediately but in revoked state. This is a deliberate architecture decision that belongs in the M6 "Advanced Tracker Delay-Loading" milestone where the tracker injection system is being redesigned anyway.

**Implementation plan for M6:**
1. Remove FB Pixel from the block list (don't block the script tag).
2. Add `fbq('consent', 'revoke')` before `fbq('init', ...)` in the inline script.
3. In `consentToScripts()` (JS): add `if (typeof fbq === 'function') { fbq('consent', 'grant'); }` before `insertBlockedScripts()`.
4. In `rejectConsent()` (JS): no action needed — `'revoke'` state persists.

---

### 3. Microsoft Clarity — No Consent API

**What it is:**  
Microsoft Clarity does not have a native consent API comparable to Google Consent Mode or FB Pixel.

**How Clarity handles consent:**  
The recommended GDPR approach is simply to not load Clarity until consent is given. This is what we already do (Clarity is blocked until consent via `insertBlockedScripts()`).

**Feasibility:** N/A — existing block mechanism is correct.

**Recommendation:** No change needed. Continue blocking Clarity until consent is accepted. Document this clearly for users who ask about Clarity + GDPR compliance.

---

### 4. IAB Transparency and Consent Framework (TCF v2.2)

**What it is:**  
The IAB TCF is an industry-wide standard for consent signalling between Consent Management Platforms (CMPs) and ad networks. It uses a `__tcfapi()` global function and a standardised binary consent string (TC String) stored in a first-party cookie (`euconsent-v2`).

**Why it's complex:**  
- Requires registration as an IAB CMP (a formal process with costs).
- The TC String encodes consent for hundreds of vendors using a specific binary format.
- Requires significant infrastructure: a vendor list CDN, GVL (Global Vendor List) fetching, UI for granular vendor selection.
- Ad networks and DSPs check the TC String via `__tcfapi()` before firing.

**Feasibility:** ❌ **Too complex for this plugin — not in scope.**

**Recommendation:** Mini WP GDPR is designed for small sites and simple tracking setups (GA, FB Pixel, Clarity). Full IAB TCF compliance would require becoming a registered CMP, which is disproportionate to the plugin's scope. If a user needs full IAB TCF support, they should use a dedicated CMP like Cookiebot, Didomi, or OneTrust.

Document this limitation clearly in the user-facing README.

---

### 5. Native Browser Cookie/Consent APIs

**What it is:**  
The `navigator.cookieStore` API (Chrome 87+, partial support elsewhere) allows reading and writing cookies asynchronously. It is **not** a consent framework — it's just a modern cookie accessor.

There is no native browser "consent API" in the W3C/WHATWG sense. Proposals exist (e.g. Privacy Sandbox's Topics API, Protected Audience API) but these are advertising APIs for cookieless targeting, not consent management.

**Feasibility:** ❌ **Not applicable.** The consent problem is a UX/legal problem, not a browser API problem.

**Recommendation:** Continue using `localStorage` (with cookie fallback) for consent storage. This is cross-browser, reliable, and well-understood. The `navigator.cookieStore` API offers no advantage for our use case.

---

## Summary & Implementation Priority

| API | Feasibility | Decision | Status |
|---|---|---|---|
| Google Consent Mode v2 | ✅ High | Implement | ✅ Done (M5 Phase 5.3) |
| Facebook Pixel Consent | ✅ High | Deferred | 📋 M6 Phase 6.1 |
| Microsoft Clarity | N/A | No change needed | ✅ Done (blocking sufficient) |
| IAB TCF v2.2 | ❌ Out of scope | Document limitation | 📋 README note |
| Native Browser APIs | ❌ Not applicable | N/A | — |

---

## Technical Notes

### Google Consent Mode v2 — Output Format

The consent default is output as a single-line `<script>` block in `wp_head` at priority 1:

```html
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("consent","default",{"analytics_storage":"denied","ad_storage":"denied","ad_user_data":"denied","ad_personalization":"denied","wait_for_update":500});</script>
```

This is the format recommended by Google for Consent Mode implementations (see: https://developers.google.com/tag-platform/security/guides/consent).

### `wait_for_update` Parameter

Set to `500` ms. This tells GA to hold events in a queue for up to 500 ms after page load, waiting for a consent update call. Since our consent popup appears on DOMContentLoaded and the user may accept quickly, 500 ms is a reasonable window. Sites with slow connections or users who immediately accept may benefit from a longer value — this could be made configurable in a future sprint.

### Consent Update Timing

In `consentToScripts()`:
1. localStorage/cookie consent stored.
2. `gtag('consent', 'update', {...})` fired (queued in `dataLayer` immediately).
3. `insertBlockedScripts()` loads `gtag.js` → GA reads the queued granted state.

This ordering ensures GA never starts in a denied state after the user accepts.

### Guard Pattern

```javascript
if ( typeof gtag === 'function' ) {
    gtag( 'consent', 'update', { ... } );
}
```

This is a no-op when:
- Google Consent Mode is disabled (the `wp_head` script is not output, so `window.gtag` is undefined).
- GA tracking is disabled entirely.

No errors or side effects when Consent Mode is off.

---

## References

- [Google Consent Mode v2 developer guide](https://developers.google.com/tag-platform/security/guides/consent)
- [Facebook Pixel consent documentation](https://developers.facebook.com/docs/meta-pixel/implementation/gdpr)
- [IAB TCF v2.2 specification](https://iabeurope.eu/tcf-2-0/)
- [MDN: CookieStore API](https://developer.mozilla.org/en-US/docs/Web/API/CookieStore)

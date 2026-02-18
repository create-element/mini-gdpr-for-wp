# Troubleshooting Guide — Mini WP GDPR v2.0.0

**Last Updated:** 18 February 2026  
**Plugin version:** 2.0.0

Common issues, diagnostic steps, and solutions.

---

## Table of Contents

1. [Consent Popup Not Showing](#consent-popup-not-showing)
2. [Trackers Loading Before Consent](#trackers-loading-before-consent)
3. [Trackers Not Loading After Consent](#trackers-not-loading-after-consent)
4. [Reject Button Missing](#reject-button-missing)
5. [Consent Not Remembered Between Visits](#consent-not-remembered-between-visits)
6. [Settings Not Saving](#settings-not-saving)
7. [AJAX Errors on Accept / Reject](#ajax-errors-on-accept--reject)
8. [PHP Errors on Activation](#php-errors-on-activation)
9. [Google Analytics Issues](#google-analytics-issues)
10. [Facebook Pixel Issues](#facebook-pixel-issues)
11. [Microsoft Clarity Issues](#microsoft-clarity-issues)
12. [WooCommerce Integration Issues](#woocommerce-integration-issues)
13. [Contact Form 7 Issues](#contact-form-7-issues)
14. [PHPCS / PHPStan Errors](#phpcs--phpstan-errors)
15. [Debugging Tools](#debugging-tools)

---

## Consent Popup Not Showing

**Symptom:** No cookie consent banner appears on the front-end.

**Checklist:**

1. **Plugin is enabled** — Go to *Settings → Mini WP GDPR* and confirm "Enable cookie consent popup" is checked.
2. **Popup JavaScript is loaded** — Open browser DevTools → Network → filter for `mini-gdpr-cookie-popup`. A file `mini-gdpr-cookie-popup.min.js` (or `.js` with `SCRIPT_DEBUG=true`) should load with HTTP 200.
3. **`mgwcsData` is present** — Open DevTools Console and type `window.mgwcsData`. It should be an object. If `undefined`, the `Script_Blocker` class did not run — this usually means PHP errors exist (see error log).
4. **User has not already consented** — Open DevTools → Application → Local Storage. Look for a key matching `mgwcsData.cn` (default: `mini_gdpr_cs`). If it exists with a recent date, the popup is suppressed. Clear it to re-test.
5. **Privacy Policy page is configured** — The plugin requires a WordPress Privacy Policy page. Go to *Settings → Privacy* and ensure a page is set. If not, the settings page will show a warning and the popup may not initialise.
6. **Check PHP error log** — See [Debugging Tools](#debugging-tools).

---

## Trackers Loading Before Consent

**Symptom:** Google Analytics, Facebook Pixel, or Clarity scripts appear in the page source before the user accepts.

**How blocking works:** The plugin captures scripts at the PHP level via the `script_loader_tag` filter. Scripts registered via `wp_enqueue_script()` with recognised handles are suppressed. Inline `<script>` tags injected via `wp_head` directly (outside the enqueue system) are **not** blocked by this mechanism.

**Checklist:**

1. **Check which script is loading** — View page source and search for `gtag.js`, `fbevents.js`, or `clarity.ms`. If you see the SDK URL (not just a stub/stub data layer), it is loading before consent.
2. **Google Analytics** — The plugin injects a gtag stub + consent defaults into `<head>`. The `gtag.js` SDK itself should be absent from the source until consent is given. If you see `gtag.js`, another plugin (e.g. Site Kit, MonsterInsights) may be injecting it independently. Use the *Settings → Mini WP GDPR → Google Analytics* section to check the "external injector" warning.
3. **Another plugin is injecting the tracker** — Disable other analytics or SEO plugins to test. The `mwg_is_external_ga_injector_plugin_installed()` check only detects common conflicts; a custom integration may be missed.
4. **Script handle not recognised** — The plugin matches GA by the captured inline config script pattern, not by script handle. If another plugin loads GA with a non-standard approach, it may not be captured. Check `Script_Blocker::get_blockable_scripts()` for the full handle list.

---

## Trackers Not Loading After Consent

**Symptom:** User accepted consent, but Google Analytics / Facebook Pixel / Clarity never fires (verified via browser DevTools Network or tag debugger).

**Checklist:**

1. **Verify `mgwcsData` has the tracker ID** — In DevTools Console: `window.mgwcsData.gaId` (or `.fbpxId`, `.clarityId`). If empty/undefined, the tracker is not enabled in settings.
2. **Verify the tracker is enabled** — Go to *Settings → Mini WP GDPR* and confirm the tracker is switched on with a valid ID/code.
3. **Check for JS errors** — Open DevTools Console for errors before or during `consentToScripts()`. Any uncaught error may abort the load sequence.
4. **Inspect the `loadGoogleAnalytics()` call** — In DevTools Sources, set a breakpoint in `mini-gdpr-cookie-popup.min.js` or use the unminified source. Confirm `loadGoogleAnalytics()` is called.
5. **Check Content Security Policy (CSP)** — If the site has a CSP header or meta tag, it may block `googletagmanager.com`, `connect.facebook.net`, or `clarity.ms`. Check the browser Console for CSP errors.
6. **Google Analytics: Consent Mode may be blocking** — If GA Consent Mode is enabled, `gtag('consent','update',{granted})` must fire before data is sent. This is handled automatically in `loadGoogleAnalytics()` — confirm there are no JS errors before that call.

---

## Reject Button Missing

**Symptom:** The consent popup shows Accept and Info buttons but no Reject button.

**Cause:** The Reject button was added in v2.0.0. If upgrading from v1.x, the new CSS and JS assets must be loaded.

**Solution:**
1. Clear all caches (server cache, CDN, browser cache).
2. If using a caching plugin, purge the asset cache.
3. Run the build process to regenerate minified assets: `npm run build` from the plugin root.
4. Verify `mini-gdpr-cookie-popup.min.js` is the v2.0.0 file (check file modification date or `PP_MWG_VERSION` in the page source).

---

## Consent Not Remembered Between Visits

**Symptom:** The consent popup reappears on every visit despite the user having accepted.

**Checklist:**

1. **LocalStorage is disabled** — Some browsers in strict privacy mode block localStorage. The plugin falls back to a cookie (`document.cookie`). Check if the cookie fallback is also blocked (e.g. in Firefox with "Delete cookies and site data when Firefox is closed" enabled).
2. **Consent duration has expired** — The default consent duration is 365 days. If you reduced this in settings, previously stored consents may have expired. Check `mgwcsData.cd` for the current duration.
3. **Consent key mismatch** — If the localStorage key was changed between versions, old consents won't be found. The key is `mini_gdpr_cs` (stored in `mgwcsData.cn`). Verify this matches what's stored in DevTools → Application → Local Storage.
4. **Popup is always-on** — Check *Settings → Mini WP GDPR* for "Always show consent popup" option — if enabled, the popup shows on every page regardless of stored consent.

---

## Settings Not Saving

**Symptom:** Changes to the settings page do not persist after saving.

**Checklist:**

1. **Nonce verification failing** — The settings form uses a custom nonce. If caching middleware serves a cached form, the nonce expires before submission. Disable caching for the admin dashboard.
2. **Capability check** — Only users with `manage_options` capability can save settings. Confirm you are logged in as an administrator.
3. **PHP errors** — A fatal error during form processing prevents saving. Check the PHP error log.
4. **`DISALLOW_FILE_EDIT` or locked settings** — Some hosting environments restrict options writes. Check if `update_option()` returns `false` by temporarily adding a debug log.

---

## AJAX Errors on Accept / Reject

**Symptom:** The browser console shows a 400, 403, 429, or 500 response when accepting or rejecting consent.

| HTTP code | Meaning | Action |
|---|---|---|
| 400 Bad Request | Missing nonce or invalid action | Check that `mgwcsData.acceptNonce` / `rejectNonce` is present for logged-in users |
| 403 Forbidden | Nonce verification failed | Page may be cached with a stale nonce — purge cache |
| 429 Too Many Requests | Rate limit exceeded | Default: 10 consent/reject actions per hour per user. Wait before retrying |
| 500 Server Error | PHP fatal error in AJAX handler | Check PHP error log for `pp_mwg_` or `Mini_Wp_Gdpr` entries |

For non-logged-in users, `ajaxUrl`, `acceptNonce`, and `rejectNonce` are intentionally absent from `mgwcsData`. Anonymous visitors store consent client-side only — no AJAX is fired. This is expected behaviour.

---

## PHP Errors on Activation

**Symptom:** White screen or `Plugin could not be activated because it triggered a fatal error` on activation.

**Common causes:**

1. **PHP < 7.4** — The plugin uses typed properties and union types requiring PHP 7.4+. Check *Tools → Site Health* for the PHP version.
2. **Conflicting class names** — Another plugin may define a class in the global namespace that conflicts. The plugin uses the `Mini_Wp_Gdpr` namespace; conflicts should be rare but possible with poorly namespaced plugins.
3. **Missing WordPress functions** — The plugin should only be active within WordPress. Direct PHP execution will fail the `defined( 'ABSPATH' ) || die()` guard.
4. **Composer autoloader missing** — The plugin does not use Composer autoloading in production. All files are `require`d directly in `mini-wp-gdpr.php`. If you deleted files, restore from the plugin package.

---

## Google Analytics Issues

### GA not tracking after consent

1. Check `window.mgwcsData.gaId` — must contain your `G-XXXXXXXXXX` tracking ID.
2. In GA4 DebugView (*Admin → DebugView*), verify events are arriving.
3. Check the browser Network tab for requests to `google-analytics.com` or `googletagmanager.com`.
4. If using GA4 with Consent Mode, ensure `gtag('consent','update',{granted})` fires — it is called inside `loadGoogleAnalytics()`.

### gtag.js loading on every page (not delayed)

Another plugin is injecting gtag.js directly. The *Settings* page shows a warning when a
known conflicting plugin (Site Kit, MonsterInsights, etc.) is detected. Disable GA in the
conflicting plugin and use Mini WP GDPR's built-in GA integration.

### "External GA injector detected" warning in settings

The plugin detected a known GA injector plugin is active. When this is the case, the plugin
automatically disables its own GA injection to avoid duplicate tracking. Disable the GA
option in the conflicting plugin, or disable Mini WP GDPR's GA option and let the other
plugin handle it.

---

## Facebook Pixel Issues

### Pixel not firing after consent

1. Check `window.mgwcsData.fbpxId` — must contain your numeric Pixel ID.
2. Install the [Facebook Pixel Helper](https://www.facebook.com/business/help/742478679120153) Chrome extension. After accepting consent, it should show the pixel has fired.
3. FB Pixel fires via `fbq('init', pixelId)` inside `loadFacebookPixel()`. Verify the call with a DevTools breakpoint.

### Pixel fires before consent

The plugin injects `fbq('consent','revoke')` in the PHP stub **before** the pixel initialises.
If the pixel fires before consent, another plugin or theme is loading `fbevents.js` directly.
Audit other plugins for Facebook Pixel integrations and disable them.

---

## Microsoft Clarity Issues

### Clarity not recording sessions after consent

1. Check `window.mgwcsData.clarityId` — must contain your Clarity Project ID.
2. Clarity Project IDs are 10-character alphanumeric strings (e.g. `abc123xyz0`). The plugin
   validates this format — check the PHP error log for ID validation failures.
3. The Clarity SDK is loaded dynamically via `loadMicrosoftClarity()` after consent.
   Verify the script is appended in DevTools → Elements after accepting.

---

## WooCommerce Integration Issues

### Consent checkbox not appearing on registration form

1. Confirm *WooCommerce → My Account → enable GDPR consent* is checked in settings.
2. The `woocommerce_register_form` hook must fire. Some themes customise the registration
   form — if the hook is not called, the checkbox won't appear.
3. Check the PHP error log for `Mini_Wp_Gdpr` errors on the `/my-account/` page.

### WooCommerce orders not recording consent

The plugin hooks into `woocommerce_new_order` to record consent at order time. This requires
the order to be placed by a logged-in user who has accepted GDPR. Guest orders do not record
consent (consent is client-side only for anonymous users).

---

## Contact Form 7 Issues

### Consent checkbox not installed in CF7 form

Go to *Settings → Mini WP GDPR → Contact Form 7* and click "Install consent checkbox". This
uses an AJAX handler (`mwginstcf7`) that:
1. Locates the CF7 form by ID.
2. Appends the acceptance checkbox before the `[submit]` tag.
3. Adds the field to the email body template.

If the install fails, check:
- The form ID is correct.
- The current user has `manage_options` capability.
- The `[submit]` tag exists in the form — the installer uses it as an anchor point.

### CF7 form submission not recording consent

The `wpcf7_mail_sent` hook fires on successful submission. The plugin looks up the WP user by
submitted email address. If the email doesn't match any WP user, no server-side consent is
recorded (the client-side popup consent still applies).

---

## PHPCS / PHPStan Errors

### PHPCS errors on a file you haven't touched

Run `bin/fix.sh` to auto-fix formatting. Then re-run `bin/check.sh`. Common auto-fixable issues:
- Incorrect indentation (tabs vs. spaces)
- Missing spaces around operators
- Incorrect brace placement

### PHPStan errors for WooCommerce / CF7 classes

These are expected when WooCommerce or CF7 is not installed on the dev environment.
The plugin's `phpstan.neon` includes `ignoreErrors` rules for these. If you see new
errors, verify your `phpstan.neon` matches the committed version.

### "Class not found" errors on PHPStan scan

Ensure `vendor/autoload.php` exists (run `composer install`). If PHPStan fails to load
the bootstrap, run: `vendor/bin/phpstan analyse --generate-baseline`.

---

## Debugging Tools

### PHP error log

The plugin writes diagnostic messages to the standard PHP error log via `error_log()`.

```bash
# Watch the error log in real-time on the dev server
tail -f /var/www/westfield.local/log/error.log

# Filter for plugin messages only
grep -i "Mini_Wp_Gdpr\|pp_mwg\|mini.gdpr" /var/www/westfield.local/log/error.log
```

### WordPress debug log

Ensure `WP_DEBUG` and `WP_DEBUG_LOG` are enabled in `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Then check `/wp-content/debug.log`.

### WP-CLI queries

Inspect stored consent records directly:

```bash
# Check a user's consent meta
wp --path=/var/www/westfield.local/web user meta get <user_id> _pwg_accepted_gdpr_when_first
wp --path=/var/www/westfield.local/web user meta get <user_id> _pwg_accepted_gdpr_when_recent
wp --path=/var/www/westfield.local/web user meta get <user_id> _pwg_rejected_gdpr_when

# Check plugin options
wp --path=/var/www/westfield.local/web option get mwg_is_cookie_consent_popup_enabled
wp --path=/var/www/westfield.local/web option get mwg_ga_tracking_code

# List all plugin options
wp --path=/var/www/westfield.local/web option list --search="mwg_*"
```

### SCRIPT_DEBUG mode

Set `SCRIPT_DEBUG` to `true` in `wp-config.php` to load unminified JS/CSS:

```php
define( 'SCRIPT_DEBUG', true );
```

This loads `mini-gdpr-cookie-popup.js` instead of the `.min.js` version, making it
easier to set breakpoints and trace the consent flow.

### Resetting consent state

To test the popup flow from scratch without clearing your browser:

1. Open DevTools → Application → Local Storage and delete the `mini_gdpr_cs` and `mini_gdpr_rc` keys.
2. Or call `window.mgwShowCookiePreferences()` in the Console to re-show the popup.
3. For logged-in users, reset server-side consent via Admin:
   Go to *Settings → Mini WP GDPR → Consent Statistics → Reset All Consents* (admin only).
4. Or via WP-CLI:
   ```bash
   wp --path=/var/www/westfield.local/web user meta delete <user_id> _pwg_accepted_gdpr_when_first _pwg_accepted_gdpr_when_recent _pwg_rejected_gdpr_when
   ```

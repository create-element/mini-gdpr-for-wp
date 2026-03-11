=== Mini WP GDPR ===

Contributors: powerplugins
Tags: gdpr, cookie consent, privacy, google analytics, tracking
Donate link: https://power-plugins.com/plugins/
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

A lightweight GDPR compliance plugin for WordPress with cookie consent management, tracking script control, and WooCommerce/Contact Form 7 integration.


== Description ==

Mini WP GDPR helps you manage cookie consent and tracking scripts in a GDPR-compliant way. It provides a consent popup with explicit Accept and Reject buttons, blocks tracking scripts until consent is given, and integrates with WooCommerce and Contact Form 7.

Installing this plugin is no guarantee that your site will become fully GDPR compliant, but it should make it much easier for you to get there.


== Features ==

* **Cookie consent popup** with Accept, Reject, and Info buttons — users can explicitly accept or reject tracking.
* **Script blocking** — automatically detects and blocks tracking scripts until the user consents.
* **Google Analytics** — built-in GA4 support with delay-loading and Google Consent Mode v2.
* **Facebook Pixel** — full integration with the FB Consent API (revoke before consent, grant after).
* **Microsoft Clarity** — delay-loaded after consent.
* **Custom tracker API** — register any third-party tracker via the `mwg_register_tracker` filter.
* **WooCommerce** — tracks T&C consent on checkout, optional MyAccount consent integration.
* **Contact Form 7** — automatically adds GDPR consent checkboxes to CF7 forms.
* **Accessible** — ARIA roles, keyboard Tab trapping, focus management in the consent popup.
* **No jQuery** — modern ES6+ JavaScript, Terser-minified assets.


== Help ==

* [Mini WP GDPR Documentation](https://power-plugins.com/plugin/mini-wp-gdpr/)
* [GitHub Repository](https://github.com/create-element/mini-gdpr-for-wp)


== Installation ==

1. In WordPress admin, go to **Plugins > Add New > Upload Plugin**.
2. Upload the `mini-wp-gdpr.zip` file and click **Install Now**.
3. Activate the plugin.
4. Go to **Settings > Privacy** and ensure a Privacy Policy page is set.
5. Go to **Settings > Mini WP GDPR** to configure the consent popup, trackers, and integrations.


== Frequently Asked Questions ==

= Does this make my site GDPR compliant? =

This plugin helps with GDPR compliance by managing cookie consent and tracking scripts. Full GDPR compliance also requires a proper privacy policy, data processing agreements, user data export/deletion capabilities, and a lawful basis for processing. This plugin is a tool, not a guarantee of compliance.

= How does the consent popup work? =

New visitors see a popup with three buttons: Reject, Info, and Accept. Clicking Accept loads tracking scripts and stores consent in the browser. Clicking Reject prevents all tracking scripts from loading. A floating cookie button lets visitors change their decision at any time.

= How long is consent stored? =

Default: 365 days, configurable in **Settings > Mini WP GDPR**. Consent is stored in browser localStorage (preferred) or cookies (fallback).

= Does it work with other analytics plugins? =

The plugin detects common GA injector plugins (Site Kit, WooCommerce Google Analytics Integration) and warns you if a conflict is found. For best results, use Mini WP GDPR's built-in tracker integrations instead of a separate analytics plugin.

= Can I register custom trackers? =

Yes. Use the `mwg_register_tracker` PHP filter to register any third-party tracker. Registered trackers are automatically blocked until consent and delay-loaded after acceptance. See the developer documentation on GitHub for examples.


== Screenshots ==

1. Cookie consent popup with Accept, Reject, and Info buttons
2. Plugin settings page — Cookie Consent section
3. Plugin settings page — Google Analytics section
4. Consent statistics dashboard


== Changelog ==

= 2.0.0 =
*Released 18th February 2026*

Major release — complete rewrite of the JavaScript layer, removal of the pp-core.php dependency, and significant new features.

**New features:**
* Reject button on the consent popup for GDPR-compliant explicit rejection.
* Google Consent Mode v2 support (consent defaults denied, granted on accept).
* Facebook Pixel Consent API integration (revoke/grant).
* Microsoft Clarity delay-loading (SDK only loads after consent).
* Custom tracker registration API via the `mwg_register_tracker` filter.
* Consent statistics dashboard (accepted/rejected/undecided counts).
* Customisable Accept, Reject, and Info button text.
* Rate limiting on AJAX consent endpoints.

**Improvements:**
* JavaScript rewritten in ES6+ classes. jQuery dependency removed.
* Minified JS assets with Terser build pipeline (52-67% size reduction).
* Keyboard navigation and ARIA accessibility in consent popup and info overlay.
* Server-side rejection tracking stored in user meta for logged-in users.
* PHPStan level 5 and PHPCS pass with 0 errors.

**Breaking changes:**
* The `pp-core.php` framework has been removed.
* jQuery is no longer enqueued by the plugin.
* Three `pp_mwg_*` filters renamed to `mwg_*` (old names still work via deprecation bridge).

For full upgrade details, see the [Migration Guide](https://github.com/create-element/mini-gdpr-for-wp/blob/main/docs/migration-guide.md).

= 1.4.3 =
*Released 23rd January 2026*

* Minor refactor of the popup JS to replace inline `onclick` with `addEventListener('click', ...)`, enabling support for strict CSP headers.

For older versions, see the [full changelog on GitHub](https://github.com/create-element/mini-gdpr-for-wp/releases).


== Upgrade Notice ==

= 2.0.0 =
Major update. All settings are preserved — no data migration needed. See the Migration Guide on GitHub if you have custom hooks or JavaScript integrations.

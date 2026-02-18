=== Mini WP GDPR ===

Contributors: powerplugins
Tags: gdpr, cookie notice, cookie consent, privacy policy, google analytics, microsoft clarity
Donate link: https://power-plugins.com/plugins/
Requires at least: 6.4
Tested up to: 6.9
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

A lightweight and easy-to-use tool to help you with your GDPR compliance tasks.


== Description ==

Features include a cookie and tracking-script consent popup, integrations for WooCommerce and Contact Form 7. It logs when users first accept your privacy policy and can also inject your Google Analytics tracking code for you.

Installing this plugin is no guarantee that your site will magically become fully GDPR compliant, but it should make it much easier for you to get there.


== Features ==

* Automatically add Privacy consent check boxes to your ContactForm7 forms, with the consent coming through into the contact emails.
* Detect and log Terms & Conditions consent in WooCommerce.
* Detect tracking JS scripts and, in some cases, defer them running until the user consents.
* Inject Google Analytics, Facebook Pixel and Microsoft Clarity trackers directly from Mini WP GDPR.
* Works with the official WooCommerce Google Analytics Integration plugin.
* See which WP users have given their consent in the main WP users admin table.


== Help ==

To dig deeper into the options:

* [Mini WP GDPR Documentation](https://power-plugins.com/plugin/mini-wp-gdpr/)


== Installation ==

In the WordPress Admin area...

1. Plugins
2. Add New
3. Upload Plugin
4. Select the latest `mini-wp-gdpr.zip` file and upload it.
5. Activate the plugin.


== Changelog ==

= 2.0.0 =
*Released 18th February 2026*

* Major refactor — removes the pp-core.php framework dependency entirely.
* Added **Reject** button to the consent popup for GDPR-compliant explicit rejection.
* JavaScript fully rewritten in ES6+ (classes, const/let, fetch API). jQuery dependency removed.
* Minified JS assets with Terser build pipeline (~52–67% size reduction).
* Keyboard navigation and ARIA accessibility improvements in consent popup and info overlay.
* Google Analytics delay-loading with Google Consent Mode v2 (consent defaults denied, granted on accept).
* Facebook Pixel delay-loading with fbq('consent','revoke/grant') signals.
* Microsoft Clarity delay-loading (GDPR-compliant: SDK only loads after explicit consent).
* Generic tracker registration API (PHP `mwg_register_tracker` filter) for custom trackers.
* Consent and rejection stored in localStorage (cookie fallback for strict privacy modes).
* Server-side rejection tracking stored in user meta for logged-in users.
* Admin consent statistics dashboard (total users accepted/rejected/undecided).
* Rate limiting on AJAX consent endpoints (10/hr for accept/reject; 3/5 min for reset).
* Customisable Accept, Reject, and Info button text in plugin settings.
* Comprehensive inline PHPDoc documentation on all classes and public methods.
* Developer guide, hook/filter reference, migration guide, troubleshooting guide, FAQ.
* PHPStan level 5 passes with 0 errors.
* WordPress Coding Standards (PHPCS) passes with 0 errors on all plugin PHP files.

= 1.4.3 =
*Released 23rd January 2026*

* Minor refactor of the popup JS to replace inline `onclick` with calls to `addEventListener('click', ...)`. This makes support for strict CSP headers possible.

= 1.4.2 =
*Released 14th December 2025*

* Minor update. Confirm tested with WordPress 6.9

= 1.4.1 =
*Released 9th June 2025*

* Updated the power-plugins core library to fix some warnings when running under PHP 8.4.

= 1.4.0 =
*Released 22nd November 2023*

* Added a new filter called "mwg_is_tracker_enabled" so you can dynamically enable/disable individual scripts on a per-load basis. Useful if your site has 2 or 3 integrations, but you want to dosable one of them on the front page to get a better load speed.

= 1.3.4 =
*Released 7th November 2023*

* Minor fix so that the new account registration GDPR check on the WooCommerce checkout is only run (by default) if the site's Terms & Conditions page has been set.

= 1.3.3 =
*Released 18th August 2023*

* Minor fix to the Script_Blocker class for compatibility with PHP 8.2

= 1.3.2 =
*Released 10th August 2023*

* Minor update to replace a call to the deprecated WP_Scripts::print_inline_script() function

= 1.3.1 =
*Released 10th August 2023*

* Added translations for de-DE (machine translated)
* Confirm compatibility with WordPress 6.3

= 1.3.0 =
*Released 29th July 2023*

* Added translations for en-GB
* Added translations fr-FR (machine translated)
* Updated the Power Plugins core library

= 1.2.11 =
*Released 27th April 2023*

* Updated the Power Plugins core library
* Bumped the supported version of Wordpress to 6.2.0

= 1.2.10 =
*Released 16th November 2022*

* Fixed a couple of typos in the settings page.
* Bumped the supported version of Wordpress to 6.1.1

= 1.2.9 =
*Released 7th November 2022*

* Added support for Microsoft Clarity. Go to Settings > Mini WP GDPR and enable MS Clarity there.

= 1.2.8 =
*Released 22nd August 2022*

* Added a new enable_gdpr_registration_validation filter so you can dynamically disable the GDPR checkbox requirement when creating a new account.

= 1.2.7 =
*Released 9th August 2022*

* Minor update to the support Power Plugins library, used in the settings page.

= 1.2.6 =
*Released 25th May 2022*

* Version bump to confirm compatability with WordPress 6.0.0.

= 1.2.5 =
*Released 3rd May 2022*

* Tidy up some messages in the Settings page.

= 1.2.4 =
*Released 14th April 2022*

* Fixed missing default cookie consent popup text in some cases.

= 1.2.3 =
*Released 11th April 2022*

* Minor back-end tweaks. Tidied up the settings page and CF7 integration.

= 1.2.2 =
*Released 29th March 2022*

* Fixed call to missing WP function is_plugin_active() function in some situations.

= 1.2.1 =
*Released 16th March 2022*

* Minor adjustment to the settings page, so the page title and admin menu can be translated.

= 1.2.0 =
*Released 11th March 2022*

* First version of Facebook Pixel integration. You can add your F Pixel ID into the plugin now. NOTE: FB Pixel can't be deferred until user consent, although it is detected and displayed to the user in the list of tracking scripts.
* Refactored the tracking scripts code so it's easier for us to add detection of, and user-consent for, more trackers.

= 1.1.0 =
*Released 7th March 2022*

* Detection of Contact Form 7 consent for recognised users.
* Better detection of privacy consent in the WooCommerce checkout, for existing users.
* Added an option to reset all user privacy consents. Administrators only.

= 1.0.14 =
*Released 4th March 2022*

* Customise the cookie/tracker consent popup message.
* Alert the admin when the site doesn't have a Privacy Policy page.
* Contact Form 7 integration - easliy add new consent checkboxes to CF7 forms.

= 1.0.13 =
*Released 27th February 2022*

* Added a new option to allow/block analytics tracking scripts even when logged-in as administrator. It helps to avoid skewing your analytics stats.

= 1.0.12 =
*Released 22nd February 2022*

* Minified the JS/CSS assets and removed debugging messages from the JavaScript.

= 1.0.11 =
*Released 21st February 2022*

* Updated the readme.txt to better clarify that the site owner is responsible for testing a site's GDPR compliance.

= 1.0.10 =
*Released 19th February 2022*

* Added an option to enable/disable the blocking of tracking scripts until the user consents. By default, the consent popup will report which tracking scripts are on the site, but it won't block them.

= 1.0.9 =
*Released 14th February 2022*

* Choose the on-screen position of the cookie consent box (bottom left, middle centre, etc.

= 1.0.8 =
*Released 14th February 2022*

* Added configuration option so you can change the number of days that cookie/script consent is granted for (default=365).

= 1.0.7 =
*Released 12th February 2022*

* Added cookie consent box and script blocker.
* Added the ability to inject Google Analytics JS.

= 1.0.6 =
*Released 8th February 2022*

* We can now inject the accept-terms AJAX form into the WC MyAccount dashboard - a different action to the other account endpoints.

= 1.0.5 =
*Released 3rd February 2022*

* Minor release with preliminary prep work for upcoming Contact Form 7 integration.

= 1.0.4 =
*Released 27th January 2022*

* Added a setting to make it easy to inject a GDPR/Privacy-accept checkbox into WooCommerce My Account endpoints... visible to users who haven't accepted the policy yet.

= 1.0.3 =
*Released 24th January 2022*

* Added a standard Ajax mini-form for logged-in users who haven't accepted the Privacy Policy for GDPR compliance.
* Changed the date capture slightly, so we save when the user FIRST accepted the terms, as well as the most recent date/time that they accepted the terms. Useful for if the Privacy Policy has changed since a user first accepted the terms.

= 1.0.0 =
*Released 24th January 2022*

* Initial release

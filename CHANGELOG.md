# Changelog

All notable changes to Mini WP GDPR will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned
- Complete removal of pp-core.php framework
- Improved delay-loading for Facebook Pixel
- Add "Reject" button to consent popup
- Consent API integration
- Modern settings page rebuild
- Enhanced accessibility (WCAG compliance)
- REST API endpoints for consent management

---

## [1.4.3] - 2026-01-23

### Changed
- Refactored popup JavaScript to replace inline `onclick` with `addEventListener('click', ...)` for CSP header compatibility

---

## [1.4.2] - 2025-12-14

### Changed
- Confirmed compatibility with WordPress 6.9

---

## [1.4.1] - 2025-06-09

### Fixed
- Updated Power Plugins core library to fix warnings when running under PHP 8.4

---

## [1.4.0] - 2023-11-22

### Added
- New filter `mwg_is_tracker_enabled` to dynamically enable/disable individual scripts on a per-load basis
- Useful for disabling specific trackers on certain pages (e.g., homepage) for better performance

---

## [1.3.4] - 2023-11-07

### Fixed
- New account registration GDPR check on WooCommerce checkout now only runs (by default) if the site's Terms & Conditions page has been set

---

## [1.3.3] - 2023-08-18

### Fixed
- Script_Blocker class compatibility fix for PHP 8.2

---

## [1.3.2] - 2023-08-10

### Changed
- Replaced deprecated `WP_Scripts::print_inline_script()` function call

---

## [1.3.1] - 2023-08-10

### Added
- German (de_DE) translation (machine translated)
- Confirmed compatibility with WordPress 6.3

---

## [1.3.0] - 2023-07-29

### Added
- English UK (en_GB) translation
- French (fr_FR) translation (machine translated)

### Changed
- Updated Power Plugins core library

---

## [1.2.11] - 2023-04-27

### Changed
- Updated Power Plugins core library
- Bumped supported WordPress version to 6.2.0

---

## [1.2.10] - 2022-11-16

### Fixed
- Corrected typos in settings page

### Changed
- Bumped supported WordPress version to 6.1.1

---

## [1.2.9] - 2022-11-07

### Added
- **Microsoft Clarity** integration support
- Settings option to enable/configure Microsoft Clarity tracking

---

## [1.2.8] - 2022-08-22

### Added
- New filter `enable_gdpr_registration_validation` to dynamically disable GDPR checkbox requirement when creating new accounts

---

## [1.2.7] - 2022-08-09

### Changed
- Updated Power Plugins support library used in settings page

---

## [1.2.6] - 2022-05-25

### Changed
- Confirmed compatibility with WordPress 6.0.0

---

## [1.2.5] - 2022-05-03

### Changed
- Tidied up messages in settings page

---

## [1.2.4] - 2022-04-14

### Fixed
- Missing default cookie consent popup text in some cases

---

## [1.2.3] - 2022-04-11

### Changed
- Backend tweaks to settings page
- Improved Contact Form 7 integration

---

## [1.2.2] - 2022-03-29

### Fixed
- Call to missing `is_plugin_active()` function in some situations

---

## [1.2.1] - 2022-03-16

### Changed
- Settings page title and admin menu can now be translated

---

## [1.2.0] - 2022-03-11

### Added
- **Facebook Pixel integration** - Add FB Pixel ID in plugin settings
- Detection of Facebook Pixel (displayed to user in list of tracking scripts)

### Changed
- Refactored tracking scripts code for easier addition of new trackers

### Known Issues
- Facebook Pixel cannot be deferred until user consent (technical limitation)

---

## [1.1.0] - 2022-03-07

### Added
- Detection of Contact Form 7 consent for recognized users
- Better detection of privacy consent in WooCommerce checkout for existing users
- Option to reset all user privacy consents (administrators only)

---

## [1.0.14] - 2022-03-04

### Added
- Customizable cookie/tracker consent popup message
- Admin alert when site doesn't have Privacy Policy page
- Contact Form 7 integration - easily add consent checkboxes to CF7 forms

---

## [1.0.13] - 2022-02-27

### Added
- Option to allow/block analytics tracking scripts when logged in as administrator
- Helps avoid skewing analytics statistics

---

## [1.0.12] - 2022-02-22

### Changed
- Minified JavaScript and CSS assets
- Removed debugging messages from JavaScript

---

## [1.0.11] - 2022-02-21

### Changed
- Updated readme.txt to clarify site owner responsibility for testing GDPR compliance

---

## [1.0.10] - 2022-02-19

### Added
- Option to enable/disable blocking of tracking scripts until user consent
- By default, consent popup reports tracking scripts without blocking them

---

## [1.0.9] - 2022-02-14

### Added
- Choose on-screen position of cookie consent box (bottom-left, center, etc.)

---

## [1.0.8] - 2022-02-14

### Added
- Configuration option to change consent duration in days (default: 365)

---

## [1.0.7] - 2022-02-12

### Added
- **Cookie consent box** with script blocker
- Ability to inject Google Analytics JavaScript

---

## [1.0.6] - 2022-02-08

### Added
- Inject accept-terms AJAX form into WooCommerce MyAccount dashboard

---

## [1.0.5] - 2022-02-03

### Changed
- Preliminary preparation work for Contact Form 7 integration

---

## [1.0.4] - 2022-01-27

### Added
- Setting to inject GDPR/Privacy-accept checkbox into WooCommerce MyAccount endpoints
- Visible to users who haven't accepted privacy policy

---

## [1.0.3] - 2022-01-24

### Added
- Standard AJAX mini-form for logged-in users who haven't accepted Privacy Policy
- Capture of FIRST acceptance date (in addition to most recent)
- Useful for tracking policy changes

---

## [1.0.0] - 2022-01-24

### Added
- Initial release
- Basic GDPR compliance features
- WooCommerce integration
- User consent tracking

---

## Version History Summary

| Version | Date | Key Feature |
|---------|------|-------------|
| 1.4.3 | 2026-01-23 | CSP compatibility |
| 1.4.2 | 2025-12-14 | WordPress 6.9 support |
| 1.4.1 | 2025-06-09 | PHP 8.4 compatibility |
| 1.4.0 | 2023-11-22 | Per-load tracker control |
| 1.3.0 | 2023-07-29 | Translations added |
| 1.2.9 | 2022-11-07 | Microsoft Clarity support |
| 1.2.0 | 2022-03-11 | Facebook Pixel integration |
| 1.1.0 | 2022-03-07 | Contact Form 7 detection |
| 1.0.14 | 2022-03-04 | CF7 full integration |
| 1.0.7 | 2022-02-12 | Cookie consent & script blocking |
| 1.0.0 | 2022-01-24 | Initial release |

---

[Unreleased]: https://github.com/create-element/mini-gdpr-for-wp/compare/v1.4.3...HEAD
[1.4.3]: https://github.com/create-element/mini-gdpr-for-wp/compare/v1.4.2...v1.4.3
[1.4.2]: https://github.com/create-element/mini-gdpr-for-wp/compare/v1.4.1...v1.4.2
[1.4.1]: https://github.com/create-element/mini-gdpr-for-wp/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/create-element/mini-gdpr-for-wp/compare/v1.3.4...v1.4.0

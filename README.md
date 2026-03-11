# Mini WP GDPR

**Version:** 2.0.0
**Requires:** WordPress 6.4+ / PHP 8.0+
**License:** GPLv2 or later

A lightweight GDPR compliance plugin for WordPress with cookie consent management, tracking script control, and WooCommerce/Contact Form 7 integration.

**[View on WordPress.org](https://wordpress.org/plugins/mini-wp-gdpr/)**

---

## Features

- **Cookie consent popup** with Accept, Reject, and Info buttons (GDPR-compliant explicit rejection)
- **Script blocking** — automatically detects and blocks tracking scripts until user consent
- **Google Analytics** with delay-loading and Google Consent Mode v2
- **Facebook Pixel** with FB Consent API (revoke/grant)
- **Microsoft Clarity** with delay-loading after consent
- **Custom trackers** — register any third-party tracker via the `mwg_register_tracker` filter
- **WooCommerce** — T&C consent on checkout, optional MyAccount integration
- **Contact Form 7** — automatic GDPR consent checkboxes
- **Accessible** — ARIA roles, keyboard Tab trapping, focus management
- **Modern codebase** — namespaced PHP, ES6+ JavaScript (no jQuery), Terser-minified assets

---

## Installation

1. Download the latest release `.zip` from [WordPress.org](https://wordpress.org/plugins/mini-wp-gdpr/) or [GitHub releases](https://github.com/create-element/mini-gdpr-for-wp/releases)
2. In WordPress admin: **Plugins > Add New > Upload Plugin**
3. Upload, install, and activate
4. Go to **Settings > Privacy** and set a Privacy Policy page
5. Go to **Settings > Mini WP GDPR** to configure

---

## Documentation

Full documentation is in the [`docs/`](docs/) directory:

- **[User Guide](docs/user-guide.md)** — Setup and configuration for site owners
- **[Developer Guide](docs/developer-guide.md)** — Architecture and extension points
- **[Hooks & Filters Reference](docs/hooks-and-filters.md)** — Complete PHP and JavaScript API
- **[Tracker Registration API](docs/tracker-registration-api.md)** — Add custom third-party trackers
- **[Migration Guide](docs/migration-guide.md)** — Upgrading from v1.x to v2.0.0
- **[Troubleshooting](docs/troubleshooting.md)** — Common issues and solutions
- **[Upgrade FAQ](docs/faq-upgrading.md)** — Frequently asked upgrade questions

---

## Quick Start for Developers

```php
// Register a custom tracker
add_filter( 'mwg_register_tracker', function ( array $trackers ) : array {
    $trackers['hotjar'] = [
        'handle'      => 'hotjar-analytics',
        'description' => 'Hotjar',
        'sdk_url'     => 'https://static.hotjar.com/c/hotjar-12345.js?sv=6',
        'pattern'     => '/hotjar-[0-9]+\.js/',
        'can_defer'   => true,
    ];
    return $trackers;
} );

// React to consent events
add_action( 'mwg_consent_accepted', function ( int $user_id ) {
    // User accepted — e.g. log to your CRM
} );

// Check consent state
if ( mwg_has_user_accepted_privacy_policy() ) {
    // Current user has accepted
}
```

```js
// Re-open consent popup from a footer link
window.mgwShowCookiePreferences();
```

---

## Development

```bash
git clone git@github.com:create-element/mini-gdpr-for-wp.git
cd mini-gdpr-for-wp
npm install          # JS dev dependencies (Terser)
node bin/build.js    # Build minified JS assets
phpcs                # WordPress Coding Standards check
phpcbf               # Auto-fix WPCS violations
```

See [docs/developer-guide.md](docs/developer-guide.md) for architecture details and coding standards.

---

## Contributing

1. Fork the repository
2. Create a feature branch
3. Follow WordPress Coding Standards (`phpcs` must pass)
4. Submit a pull request

---

## Security

Report security vulnerabilities to [hello@power-plugins.com](mailto:hello@power-plugins.com). Do not disclose security issues publicly until they have been addressed.

---

## Links

- **WordPress.org:** https://wordpress.org/plugins/mini-wp-gdpr/
- **GitHub:** https://github.com/create-element/mini-gdpr-for-wp
- **Support:** https://github.com/create-element/mini-gdpr-for-wp/issues
- **Email:** hello@power-plugins.com

---

**GPLv2 or later** | Made by [Power Plugins](https://power-plugins.com/)

# Mini WP GDPR

**Version:** 1.4.3  
**Requires:** WordPress 6.4+  
**Tested up to:** WordPress 6.9  
**License:** GPLv2 or later  
**Contributors:** Power Plugins

A lightweight and easy-to-use GDPR compliance plugin for WordPress with cookie consent management, tracking script control, and WooCommerce/Contact Form 7 integration.

---

## 🚀 Features

### Cookie Consent Management
- **Smart Cookie Popup** - Displays a consent popup for visitors before loading tracking scripts
- **Script Blocking** - Automatically detects and blocks tracking scripts until user consent
- **Local Storage** - Stores consent preferences in browser localStorage with configurable duration
- **Respects DNT** - Optionally respect Do Not Track browser settings

### Tracking Integrations
- **Google Analytics** - Built-in GA4/Universal Analytics support
- **Facebook Pixel** - Full Facebook Pixel integration
- **Microsoft Clarity** - Microsoft Clarity tracking support
- **Third-Party Scripts** - Detects and blocks common third-party tracking scripts

### WordPress Integration
- **WooCommerce** - Tracks T&C consent on checkout, optional MyAccount integration
- **Contact Form 7** - Automatically adds GDPR consent checkboxes to CF7 forms
- **User Tracking** - Records when users accept privacy policy (first & most recent)
- **Admin Dashboard** - View user consent status in WordPress Users table

### Developer Features
- **Extensible Hooks** - Filters and actions for custom tracking scripts
- **Script Detection** - Pattern-based detection system for blocking scripts
- **Role-Based Control** - Exclude admin/specific roles from tracking
- **Modern Codebase** - Namespaced PHP, modern JavaScript patterns

---

## 📦 Installation

### Via WordPress Admin

1. Download the latest release `.zip` file
2. Navigate to **Plugins → Add New → Upload Plugin**
3. Upload the `.zip` file and click **Install Now**
4. Activate the plugin
5. Navigate to **Settings → Mini WP GDPR** to configure

### Via Composer

```bash
composer require power-plugins/mini-wp-gdpr
```

### Manual Installation

1. Upload `mini-wp-gdpr/` directory to `/wp-content/plugins/`
2. Activate through the **Plugins** menu in WordPress
3. Configure via **Settings → Mini WP GDPR**

---

## ⚙️ Configuration

### Basic Setup

1. **Create Privacy Policy Page**
   - Navigate to **Settings → Privacy** in WordPress
   - Create/select your Privacy Policy page
   - This is required for the plugin to activate

2. **Configure Cookie Consent**
   - Go to **Settings → Mini WP GDPR**
   - Enable "Cookie Consent Popup"
   - Customize consent duration (default: 365 days)
   - Choose popup position (bottom-left, bottom-right, etc.)

3. **Add Tracking Scripts**
   - Enter your **Google Analytics** tracking ID (GA4 or UA format)
   - Enter your **Facebook Pixel** ID
   - Enter your **Microsoft Clarity** ID
   - Enable/disable individual trackers as needed

### WooCommerce Integration

- **Checkout Consent** - Automatically tracks T&C acceptance during checkout
- **MyAccount Injection** - Display consent status/acceptance form in MyAccount area
- **Order Metadata** - Stores consent timestamp with order data

### Contact Form 7 Integration

- Automatically adds privacy policy consent checkbox to CF7 forms
- Includes consent status in email notifications
- One-click installation of GDPR-compliant forms

---

## 🎨 Customization

### CSS Customization

The popup uses these CSS classes:

```css
#mgwcsCntr          /* Main popup container */
.mgw-pos-bl         /* Position: bottom-left */
.mgw-pos-br         /* Position: bottom-right */
.mgw-pos-tl         /* Position: top-left */
.mgw-pos-tr         /* Position: top-right */
```

Add custom CSS via **Appearance → Customize → Additional CSS**

### Hooks & Filters

#### Filters

```php
// Modify blockable script handles
add_filter('mwg_blockable_script_handles', function($handles) {
    $handles[] = 'my-custom-tracker';
    return $handles;
});

// Define custom tracker pattern
add_filter('mwg_tracker_my-custom-tracker', function() {
    return [
        'pattern' => '/example\\.com\\/tracker\\.js/',
        'field' => 'src',
        'description' => 'My Custom Tracker',
        'can-defer' => true
    ];
});

// Disable tracking for specific pages
add_filter('mwg_is_tracker_enabled', function($enabled, $handle) {
    if (is_front_page() && $handle === 'mgw-facebook-pixel') {
        return false; // Disable FB Pixel on homepage
    }
    return $enabled;
}, 10, 2);

// Customize roles excluded from tracking
add_filter('mwg_dont_track_roles', function($roles) {
    $roles[] = 'editor';
    return $roles;
});
```

#### Actions

```php
// Inject custom tracker
add_action('mwg_inject_tracker_my-custom-tracker', function() {
    wp_enqueue_script('my-tracker', 'https://example.com/tracker.js');
});

// Run code after consent
add_action('wp_footer', function() {
    ?>
    <script>
    if (localStorage.getItem('<?php echo COOKIE_NAME_BASE; ?>')) {
        // User has consented
        console.log('User consented to tracking');
    }
    </script>
    <?php
});
```

---

## 🛠️ Development

### Requirements

- PHP 8.0+
- WordPress 6.4+
- PHPCS with WPCS installed globally (for development only)
- No runtime dependencies - plugin is fully self-contained

### Setup Development Environment

```bash
# Clone repository
git clone git@github.com:create-element/mini-gdpr-for-wp.git
cd mini-gdpr-for-wp

# Install PHPCS globally (one-time setup)
# See dev-notes/workflows/code-standards.md for installation guide

# Run code standards check
phpcs

# Auto-fix code standards
phpcbf
```

### Code Standards

This plugin follows **WordPress Coding Standards**. Before committing:

```bash
# Check for violations
phpcs

# Auto-fix violations
phpcbf

# Verify fixes
phpcs
```

### Project Structure

```
mini-wp-gdpr/
├── mini-wp-gdpr.php          # Main plugin file
├── constants.php              # Plugin constants
├── functions.php              # Public API functions
├── functions-private.php      # Internal helper functions
├── includes/                  # Core classes
│   ├── class-plugin.php
│   ├── class-settings.php
│   ├── class-user-controller.php
│   ├── class-script-blocker.php
│   ├── class-admin-hooks.php
│   ├── class-public-hooks.php
│   └── class-cf7-helper.php
├── admin-templates/           # Admin UI templates
├── public-templates/          # Frontend templates
├── assets/                    # JavaScript/CSS assets
├── trackers/                  # Tracker integrations
├── languages/                 # Translation files
└── dev-notes/                 # Development documentation
```

---

## 🔒 Security

### Nonce Verification

All AJAX requests are protected with WordPress nonces:

```php
wp_verify_nonce($_POST['nonce'], ACCEPT_GDPR_ACTION);
```

### Data Sanitization

User input is sanitized before processing:

```php
$email = sanitize_email(wp_unslash($_POST['email']));
$text = sanitize_text_field(wp_unslash($_POST['text']));
```

### Output Escaping

All output is escaped for security:

```php
echo esc_html($user_text);
echo esc_url($link);
echo esc_attr($attribute);
```

### Reporting Security Issues

Please report security vulnerabilities to: [hello@power-plugins.com](mailto:hello@power-plugins.com)

Do not disclose security issues publicly until they have been addressed.

---

## 🌍 Translations

Mini WP GDPR is translation-ready. Included translations:

- 🇬🇧 English (en_GB)
- 🇩🇪 German (de_DE)
- 🇫🇷 French (fr_FR)

### Contributing Translations

1. Generate POT file: `wp i18n make-pot . languages/mini-wp-gdpr.pot`
2. Use Poedit or similar tool to create `.po` file
3. Submit via pull request

---

## 📝 Frequently Asked Questions

### Does this make my site GDPR compliant?

This plugin helps with GDPR compliance by managing cookie consent and tracking scripts. However, full GDPR compliance requires:
- Proper privacy policy
- Data processing agreements
- User data export/deletion capabilities
- Lawful basis for processing

**This plugin is a tool, not a guarantee of compliance.**

### How long is consent stored?

Default: 365 days. Configurable in **Settings → Mini WP GDPR**.

Consent is stored in browser localStorage (preferred) or cookies (fallback).

### Can I customize the popup message?

Currently, the popup uses default messages. Custom messaging will be added in a future release.

To customize now, use JavaScript to modify `mgwcsData.msg`, `mgwcsData.ok`, and `mgwcsData.mre`.

### Does it work with Google Tag Manager?

Yes! GTM scripts are detected and blocked. The plugin specifically looks for `googletagmanager.com` patterns.

### What happens if JavaScript is disabled?

If JavaScript is disabled, the consent popup won't appear and tracking scripts won't load. This is the most privacy-friendly default behavior.

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Follow WordPress Coding Standards** (run `phpcs` before committing)
4. **Write clear commit messages** (see [dev-notes/workflows/commit-to-git.md](dev-notes/workflows/commit-to-git.md))
5. **Submit a pull request**

### Development Guidelines

- See [.github/copilot-instructions.md](.github/copilot-instructions.md) for coding standards
- See [dev-notes/patterns/](dev-notes/patterns/) for implementation patterns
- See [dev-notes/workflows/](dev-notes/workflows/) for development workflows

---

## 📄 License

**GPLv2 or later**

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

---

## 🔗 Links

- **Plugin Homepage:** https://power-plugins.com/plugin/mini-wp-gdpr/
- **Documentation:** https://power-plugins.com/plugin/mini-wp-gdpr/docs/
- **Support:** https://power-plugins.com/support/
- **GitHub:** https://github.com/create-element/mini-gdpr-for-wp

---

## 💡 Support

Need help? Here's how to get support:

1. **Documentation** - Check the [official documentation](https://power-plugins.com/plugin/mini-wp-gdpr/)
2. **GitHub Issues** - Report bugs or request features on [GitHub](https://github.com/create-element/mini-gdpr-for-wp/issues)
3. **Email Support** - Contact [hello@power-plugins.com](mailto:hello@power-plugins.com)

---

**Made with ❤️ by [Power Plugins](https://power-plugins.com/)**

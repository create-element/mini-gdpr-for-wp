# User Guide — Mini WP GDPR v2.0.0

**Last Updated:** 18 February 2026  
**Plugin version:** 2.0.0

Step-by-step instructions for site owners and administrators.

---

## Table of Contents

1. [Before You Start](#before-you-start)
2. [Activation Checklist](#activation-checklist)
3. [Configuring the Consent Popup](#configuring-the-consent-popup)
4. [Setting Up Google Analytics](#setting-up-google-analytics)
5. [Setting Up Facebook Pixel](#setting-up-facebook-pixel)
6. [Setting Up Microsoft Clarity](#setting-up-microsoft-clarity)
7. [WooCommerce Integration](#woocommerce-integration)
8. [Contact Form 7 Integration](#contact-form-7-integration)
9. [Consent Statistics](#consent-statistics)
10. [Managing User Consent](#managing-user-consent)
11. [Common Tasks](#common-tasks)

---

## Before You Start

### Requirements

- WordPress 6.4 or higher
- PHP 7.4 or higher
- A Privacy Policy page configured in *Settings → Privacy*

### Important: Privacy Policy Page

Mini WP GDPR **requires** a Privacy Policy page to be set before it will activate the consent popup. The plugin reads the privacy policy URL to include it in the consent message.

**To set up your Privacy Policy page:**

1. Create a page in WordPress (e.g. *Pages → Add New*)
2. Title it "Privacy Policy" and add your policy content
3. Go to *Settings → Privacy*
4. Select your privacy policy page from the dropdown
5. Click *Use This Page*

Once this is done, the plugin will enable itself automatically.

---

## Activation Checklist

After installing and activating the plugin:

1. **Set Privacy Policy page** — See [Before You Start](#before-you-start) above
2. **Go to Settings → Mini WP GDPR** — Review all settings
3. **Enable the consent popup** — Check "Enable the cookie consent popup for new visitors"
4. **Configure your trackers** — Add your GA, FB Pixel, or Clarity IDs in the tracker sections
5. **Visit the front-end** — Open your site in a private/incognito browser window to see the popup
6. **Accept/reject cookies** — Verify trackers load (or don't) as expected

---

## Configuring the Consent Popup

### Cookie Consent Section (Settings → Mini WP GDPR)

**Enable the cookie consent popup**  
Turn the popup on or off site-wide. Disable this only if you have another cookie consent mechanism.

**Popup consent message**  
The text shown in the popup. Supports basic HTML tags (`<strong>`, `<em>`, `<a>`). The default message references your privacy policy page automatically.

**Show popup even if no tracking scripts are found**  
By default, the popup only shows when tracking scripts are configured. Enable this if you want the popup to appear unconditionally.

**How many days is consent valid?**  
Consent is stored in the visitor's browser (localStorage with a cookie fallback). The default is 365 days. After this period, the popup re-appears.

**Position of the consent box**  
Choose where on the screen the popup appears. Options: corners, edges, and centre of the page.

### Button Text

Customise the three buttons on the consent popup:

- **Accept button** — Default: "Accept cookies"
- **Reject button** — Default: "Reject"
- **"More info" button** — Default: "More info" (opens the tracker information overlay)

Leave any field blank to use the default text.

### How Visitors Interact With the Popup

When a new visitor arrives:

1. The popup appears in the configured position
2. The visitor can:
   - **Accept** — Tracking scripts load; consent stored for the configured duration
   - **Reject** — No tracking scripts load; rejection stored for the configured duration
   - **More info** — Opens an overlay listing all configured trackers; visitor can still accept or reject from there
3. A small 🍪 button appears at the bottom of the page so visitors can change their preference later

---

## Setting Up Google Analytics

### Prerequisites

- A Google Analytics 4 property
- Your measurement ID (format: `G-XXXXXXXXXX`)

### Configuration

1. Go to *Settings → Mini WP GDPR*
2. Scroll to the **Google Analytics** section
3. Check **Enable Google Analytics tracking**
4. Enter your **GA4 Measurement ID** (e.g. `G-260YT895XT`)
5. Optionally check **Enable Google Consent Mode v2** — recommended for GDPR-compliant sites; this sends consent signals to Google before any tracking occurs
6. Check **Don't track site administrators** — prevents your own visits from being counted
7. Click **Save Settings**

### How It Works

With Mini WP GDPR, Google Analytics **never loads until a visitor accepts cookies**. Instead:

- A lightweight JavaScript stub (`gtag`) is placed in the `<head>` to queue any events
- If Google Consent Mode v2 is enabled, consent defaults are set to "denied" immediately
- Only after the visitor clicks **Accept** does `gtag.js` actually load
- On accept, consent is updated to "granted" and queued events are replayed

This means your GA setup works exactly as normal — but in a GDPR-compliant way.

---

## Setting Up Facebook Pixel

### Prerequisites

- A Facebook Business Manager account
- Your Pixel ID (a numeric string, e.g. `1234567890`)

### Configuration

1. Go to *Settings → Mini WP GDPR*
2. Scroll to the **Facebook Pixel** section
3. Check **Enable Facebook Pixel tracking**
4. Enter your **Pixel ID**
5. Optionally enable the **noscript** fallback (for visitors with JavaScript disabled)
6. Click **Save Settings**

### How It Works

- A Facebook Pixel stub is placed in the page with consent set to `revoke` by default
- The full `fbevents.js` SDK only loads after the visitor accepts cookies
- On accept, consent is updated to `grant` and the SDK loads with full tracking

---

## Setting Up Microsoft Clarity

### Prerequisites

- A Microsoft Clarity account
- Your Clarity Project ID (a short alphanumeric string, e.g. `abcde12345`)

### Configuration

1. Go to *Settings → Mini WP GDPR*
2. Scroll to the **Microsoft Clarity** section
3. Check **Enable Microsoft Clarity tracking**
4. Enter your **Clarity Project ID**
5. Click **Save Settings**

### How It Works

- A lightweight queue stub is injected into the page
- The full Clarity SDK only loads after the visitor accepts cookies
- All interactions queued before consent are replayed once the SDK loads

---

## WooCommerce Integration

When WooCommerce is active, Mini WP GDPR adds an extra layer of GDPR compliance:

### Registration Form Checkbox

A GDPR consent checkbox is automatically added to the WooCommerce registration form. New customers must tick this before creating an account.

### My Account Page

Logged-in customers can view and manage their GDPR consent on the My Account page. If they previously accepted, they can see when they accepted. If they revoke, the change is recorded.

### Consent in Orders

When an order is placed, the customer's consent status is recorded against their user account.

### Settings

In the **WooCommerce** settings section:

- **Enable WooCommerce checkout consent** — Adds the checkbox to the registration form
- **Enable My Account consent toggle** — Shows the consent form in the My Account area

---

## Contact Form 7 Integration

When Contact Form 7 is active, you can automatically add a GDPR consent checkbox to your forms.

### Adding the Checkbox

1. Go to *Settings → Mini WP GDPR*
2. Scroll to the **Contact Form 7** section
3. Select the form you want to update from the dropdown
4. Click **Install consent box**

The plugin adds an acceptance checkbox before the Submit button and records the user's consent when the form is submitted.

### What Gets Recorded

When a visitor submits a CF7 form with the consent checkbox ticked, Mini WP GDPR:
- Looks up their WordPress user account (if they're logged in)
- Records their GDPR acceptance with a timestamp

---

## Consent Statistics

Go to *Settings → Mini WP GDPR* and scroll to the **Consent Statistics** section to see:

- **Total registered users** — All WordPress users on the site
- **Accepted** — Users who have accepted GDPR terms
- **Rejected** — Users who have rejected GDPR terms
- **Undecided** — Users with no recorded consent decision

> **Note:** These statistics only cover registered (logged-in) users. Anonymous visitor consent is stored in their browser (localStorage) and is not tracked server-side.

---

## Managing User Consent

### Resetting a User's Consent

Administrators can reset all user GDPR consent records:

1. Go to *Settings → Mini WP GDPR*
2. Scroll to the bottom of the page
3. Click **Reset all user privacy consents**

This clears consent records for all registered users. They will need to accept again on their next visit or login.

> **Warning:** This action cannot be undone. Use with care.

### Viewing Individual User Consent

1. Go to *Users → All Users*
2. Look for the **Privacy Consent** column
3. This shows when each user accepted the privacy policy (if they have)

---

## Common Tasks

### The popup isn't showing on my site

1. Check that you have a Privacy Policy page configured (*Settings → Privacy*)
2. Verify "Enable cookie consent popup" is checked in the plugin settings
3. Open your site in a private/incognito browser window — if you've already accepted cookies in your normal browser, the popup won't show
4. Check the browser console for JavaScript errors

For more help, see the [Troubleshooting Guide](troubleshooting.md).

### Visitors can't change their cookie preference

Make sure the popup appeared and was interacted with at least once. After that, the 🍪 manage-preferences button appears at the bottom of every page. Clicking it re-opens the consent popup.

### I want to test the popup without clearing my browser data

Open your site in a private/incognito browser window. Each new private window starts fresh with no stored consent.

### How do I remove the plugin?

Deactivate and delete it from *Plugins → Installed Plugins*. The plugin does not create custom database tables. User consent meta data (`_pwg_accepted_gdpr_when_first`, `_pwg_accepted_gdpr_when_recent`, `_pwg_rejected_gdpr_when`) will remain in the `wp_usermeta` table — use a plugin like WP-Optimize to clean up orphaned meta data after deletion.

---

*For developer documentation, see [Developer Guide](developer-guide.md).*  
*For common issues and fixes, see [Troubleshooting Guide](troubleshooting.md).*  
*For upgrading from v1.x, see [Migration Guide](migration-guide.md).*

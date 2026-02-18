# Tracker Registration API

**Since:** 2.0.0  
**File:** `includes/class-tracker-registry.php`

The Tracker Registry provides a filter-based developer API for registering custom third-party trackers without modifying Mini WP GDPR's core code.

Registered trackers are automatically:
- Added to the blockable-script-handles list (detected by Script_Blocker)
- Wired into the metadata filter system (`mwg_tracker_{handle}`)
- Delay-loaded by the consent popup JS after the user accepts tracking

---

## Basic Usage

```php
add_filter( 'mwg_register_tracker', function ( $trackers ) {
    $trackers['hotjar'] = [
        'handle'      => 'hotjar-analytics',
        'description' => 'Hotjar',
        'sdk_url'     => 'https://static.hotjar.com/c/hotjar-12345.js?sv=6',
        'pattern'     => '/hotjar-[0-9]+\.js/',
    ];
    return $trackers;
} );
```

This is all you need. After consent, the JS will dynamically inject `hotjar-analytics`'s SDK URL into `<head>`.

---

## Tracker Registration Fields

| Field | Type | Required | Description |
|---|---|---|---|
| `handle` | string | ✅ | WordPress script handle to register as blockable (e.g. `hotjar-analytics`) |
| `description` | string | ✅ | Human-readable tracker name shown in the "More info" overlay |
| `sdk_url` | string | ✅ | Full URL of the tracker SDK to inject after consent |
| `pattern` | string | ✅ | Regex (with delimiters) to match the tracker in registered scripts, e.g. `/hotjar-[0-9]+\.js/` |
| `field` | string | ❌ | What to match against: `'src'` (default) or `'outerhtml'` |
| `can_defer` | bool | ❌ | `true` = suppress the script tag before consent (when "block until consent" is enabled). Default `false` |

---

## How It Works

The generic tracker framework handles trackers that follow the simplest delay-load pattern: **inject an SDK URL after consent**.

For trackers with no stub or queue (most analytics tools), the registration handles everything automatically.

### Execution flow

1. **PHP load time:** `mwg_register_tracker` filter fires → tracker registered in `Tracker_Registry`
2. **`wp_enqueue_scripts`:** Script_Blocker runs, detects tracker handles, reads metadata
3. **`mgwcsData.trackers`:** SDK URLs for registered trackers passed to consent popup JS
4. **User accepts:** `loadCustomTrackers()` injects each `sdkUrl` as an async `<script>` tag

---

## Common Examples

### Hotjar

```php
add_filter( 'mwg_register_tracker', function ( $trackers ) {
    $hotjar_id = get_option( 'my_hotjar_site_id' );

    if ( ! empty( $hotjar_id ) ) {
        $trackers['hotjar'] = [
            'handle'      => 'hotjar-analytics',
            'description' => 'Hotjar',
            'sdk_url'     => 'https://static.hotjar.com/c/hotjar-' . absint( $hotjar_id ) . '.js?sv=6',
            'pattern'     => '/hotjar-[0-9]+\.js/',
        ];
    }

    return $trackers;
} );
```

### Heap Analytics

```php
add_filter( 'mwg_register_tracker', function ( $trackers ) {
    $heap_id = get_option( 'my_heap_app_id' );

    if ( ! empty( $heap_id ) ) {
        $trackers['heap'] = [
            'handle'      => 'heap-analytics',
            'description' => 'Heap Analytics',
            'sdk_url'     => 'https://cdn.heapanalytics.com/js/heap-' . absint( $heap_id ) . '.js',
            'pattern'     => '/heap-[0-9]+\.js/',
        ];
    }

    return $trackers;
} );
```

### Mixpanel

```php
add_filter( 'mwg_register_tracker', function ( $trackers ) {
    $trackers['mixpanel'] = [
        'handle'      => 'mixpanel-analytics',
        'description' => 'Mixpanel',
        'sdk_url'     => 'https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js',
        'pattern'     => '/mixpanel-2-latest/',
    ];
    return $trackers;
} );
```

---

## When NOT to Use This API

The generic tracker framework uses a simple pattern: **inject SDK URL after consent, no pre-consent signals**.

For trackers that require special handling, implement a dedicated tracker file in `trackers/` instead:

| Tracker | Reason |
|---|---|
| Facebook Pixel | Requires `fbq('consent','grant')` before SDK load |
| Google Analytics | Requires `gtag('consent','update',{...granted})` before SDK load + Consent Mode v2 |
| Microsoft Clarity | No consent API, but requires a PHP stub for the `window.clarity` queue |

These are already implemented in `trackers/` and use their own `loadFacebookPixel()`, `loadGoogleAnalytics()`, and `loadMicrosoftClarity()` methods in the consent popup JS.

---

## Advanced: Implementing a Full Custom Tracker

For trackers needing a pre-consent stub or consent API signals:

1. Create `trackers/tracker-{name}.php` with:
   - `mwg_blockable_script_handles` filter to register the handle
   - `mwg_tracker_{handle}` filter to provide metadata (pattern, description, can-defer)
   - `mwg_inject_tracker_{handle}` action to output the stub

2. Require it from `mini-wp-gdpr.php`

3. Pass the tracker ID to `mgwcsData` in `Script_Blocker::capture_blocked_script_handles()`

4. Add a `loadMyTracker()` method to `MiniGdprPopup` in `assets/mini-gdpr-cookie-popup.js`

5. Call it in `consentToScripts()` and the `hasConsented()` branch of `init()`

See the existing tracker files as reference implementations.

---

**Last Updated:** 18 February 2026

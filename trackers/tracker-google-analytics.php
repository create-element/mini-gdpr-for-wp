<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

add_filter('mwg_blockable_script_handles', function ($handles) {
    $handles[] = GA_SCRIPT_HANDLE;

    return $handles;
});

add_filter('mwg_tracker_' . GA_SCRIPT_HANDLE, function () {
    return [
        'pattern' => '/googletagmanager\\.com/',
        'field' => 'src',
        'description' => __('Google Analytics', 'mini-wp-gdpr')
    ];
});

add_action('mwg_inject_tracker_' . GA_SCRIPT_HANDLE, function () {
    $settings = get_settings_controller();

    $is_tracking_enabled = $settings->get_bool(OPT_IS_GA_TRACKING_ENABLED);

    if (!$is_tracking_enabled) {
        // ...
    } elseif (empty(($tracker_code = $settings->get_string(OPT_GA_TRACKING_CODE)))) {
        error_log(__FUNCTION__ . ' Missing Google Analytics tracker code');
    } elseif (!preg_match('/^(G|UA|YT|MO)-[a-zA-Z0-9-]+$/', $tracker_code)) {
        error_log(__FUNCTION__ . ' Invalid Google Analytics tracker code');
    } else {
        wp_enqueue_script(GA_SCRIPT_HANDLE, 'https://www.googletagmanager.com/gtag/js?id=' . $tracker_code, null, false);

        wp_add_inline_script(
            GA_SCRIPT_HANDLE,
            sprintf(
                'window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag(\'js\', new Date());
gtag(\'config\', \'%s\');
',
                wp_strip_all_tags($tracker_code)
            ),
            'after'
        );
    }
});

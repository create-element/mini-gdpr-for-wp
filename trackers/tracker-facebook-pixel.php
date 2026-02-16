<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

add_filter('mwg_blockable_script_handles', function ($handles) {
    $handles[] = FB_PIXEL_SCRIPT_HANDLE;

    return $handles;
});

add_filter('mwg_tracker_' . FB_PIXEL_SCRIPT_HANDLE, function () {
    return [
        'pattern' => '/connect\\.facebook\\.net\/.*\/fbevents.js/',
        'field' => 'outerhtml',
        'description' => __('Facebook Pixel', 'mini-wp-gdpr'),
        'can-defer' => false
    ];
});

add_action('mwg_inject_tracker_' . FB_PIXEL_SCRIPT_HANDLE, function () {
    $settings = get_settings_controller();

    $is_tracking_enabled = $settings->get_bool(OPT_IS_FB_PIXEL_TRACKING_ENABLED);

    if (!$is_tracking_enabled) {
        // ...
    } elseif (empty(($tracker_id = $settings->get_string(OPT_FB_PIXEL_ID)))) {
        error_log(__FUNCTION__ . ' Missing Facebok Pixel tracker code');
        // } elseif (!preg_match('/^(G|UA|YT|MO)-[a-zA-Z0-9-]+$/', $tracker_id)) {
        // 	error_log(__FUNCTION__ . ' Invalid Google Analytics tracker code');
    } else {
        wp_register_script(FB_PIXEL_SCRIPT_HANDLE, '');
        wp_enqueue_script(FB_PIXEL_SCRIPT_HANDLE);

        wp_add_inline_script(
            FB_PIXEL_SCRIPT_HANDLE,
            sprintf(
                '!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,\'script\',
\'https://connect.facebook.net/en_US/fbevents.js\');
fbq(\'init\', \'%s\');
fbq(\'track\', \'PageView\');
',
                wp_strip_all_tags($tracker_id)
            )
        );
    }
});

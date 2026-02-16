<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

add_filter('mwg_blockable_script_handles', function ($handles) {
    $handles[] = MS_CLARITY_SCRIPT_HANDLE;

    return $handles;
});

add_filter('mwg_tracker_' . MS_CLARITY_SCRIPT_HANDLE, function () {
    return [
        'pattern' => '/www\\.clarity\\.ms\/tag\//',
        'field' => 'outerhtml',
        'description' => __('Microsoft Clarity', 'mini-wp-gdpr'),
        'can-defer' => false
    ];
});

add_action('mwg_inject_tracker_' . MS_CLARITY_SCRIPT_HANDLE, function () {
    $settings = get_settings_controller();

    $is_tracking_enabled = $settings->get_bool(OPT_IS_MS_CLARITY_TRACKING_ENABLED);

    if (!$is_tracking_enabled) {
        // ...
    } elseif (empty(($tracker_id = $settings->get_string(OPT_MS_CLARITY_ID)))) {
        error_log(__FUNCTION__ . ' Missing Microsoft Clarity tracker code');
    } else {
        wp_register_script(MS_CLARITY_SCRIPT_HANDLE, '');
        wp_enqueue_script(MS_CLARITY_SCRIPT_HANDLE);

        wp_add_inline_script(
            MS_CLARITY_SCRIPT_HANDLE,
            sprintf(
                '(function(c,l,a,r,i,t,y){
  c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
  t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
  y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
})(window, document, "clarity", "script", "%s");
',
                wp_strip_all_tags($tracker_id)
            )
        );
    }
});

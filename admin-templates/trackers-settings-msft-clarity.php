<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

printf('<h3><span class="dashicons dashicons-database"></span> %s</h3>', esc_html__('Microsoft Clarity', 'mini-wp-gdpr'));

echo '<p class="pp-form-row pp-checkbox">';
echo pp_get_admin_checkbox_html(
    OPT_IS_MS_CLARITY_TRACKING_ENABLED,
    esc_html__('Add Microsoft Clarity', 'mini-wp-gdpr'),

    $settings->get_bool(OPT_IS_MS_CLARITY_TRACKING_ENABLED),
    true
);

echo '</p>';

printf('<section %s class="mt-2 ml-3">', $settings->get_bool(OPT_IS_MS_CLARITY_TRACKING_ENABLED) ? '' : 'style="display:none;"');

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
    '<label for="%s">%s</label><span class="pp-help">%s</span><input id="%s" name="%s" type="text" value="%s" />',
    esc_attr($control_id),
    esc_html__('Microsfot Clarity Project Code', 'mini-wp-gdpr'),
    esc_html__('e.g. "abcdefg123"', 'mini-wp-gdpr'),
    esc_attr($control_id),
    OPT_MS_CLARITY_ID,
    esc_attr($settings->get_string(OPT_MS_CLARITY_ID))
);
echo '</p>';

echo '</section>';

<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

printf('<h3><span class="dashicons dashicons-facebook"></span> %s</h3>', esc_html__('Facebook Pixel', 'mini-wp-gdpr'));

echo '<p class="pp-form-row pp-checkbox">';
$control_id = get_next_control_id();
printf(
    '<input id="%s" name="%s" type="checkbox" %s class="cb-section"/><label for="%s">%s</label>',
    esc_attr($control_id),
    OPT_IS_FB_PIXEL_TRACKING_ENABLED,
    $settings->get_bool(OPT_IS_FB_PIXEL_TRACKING_ENABLED) ? 'checked' : '',
    esc_attr($control_id),
    esc_html__('Add Facebook Pixel', 'mini-wp-gdpr')
);
echo '</p>';

printf('<section %s class="mt-2 ml-3">', $settings->get_bool(OPT_IS_FB_PIXEL_TRACKING_ENABLED) ? '' : 'style="display:none;"');

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
    '<label for="%s">%s</label><span class="pp-help">%s</span><input id="%s" name="%s" type="text" value="%s" />',
    esc_attr($control_id),
    esc_html__('Facebook Pixel ID', 'mini-wp-gdpr'),
    esc_html__('e.g. "999999999999999"', 'mini-wp-gdpr'),
    esc_attr($control_id),
    OPT_FB_PIXEL_ID,
    esc_attr($settings->get_string(OPT_FB_PIXEL_ID))
);
echo '</p>';

echo '</section>';

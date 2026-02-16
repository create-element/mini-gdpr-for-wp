<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

printf('<h2>%s</h2>', esc_html__('Cookie Consent', 'mini-wp-gdpr'));

echo '<p class="pp-form-row pp-checkbox">';
$control_id = get_next_control_id();
printf(
    '<input id="%s" name="%s" type="checkbox" %s class="cb-section"/><label for="%s">%s</label>',
    esc_attr($control_id),
    OPT_IS_COOKIE_CONSENT_POPUP_ENABLED,
    $settings->get_bool(OPT_IS_COOKIE_CONSENT_POPUP_ENABLED) ? 'checked' : '',
    esc_attr($control_id),
    esc_html__('Enable the cookie consent popup for new visitors (recommended)', 'mini-wp-gdpr')
);
echo '</p>';

printf('<section %s class="mt-2">', $settings->get_bool(OPT_IS_COOKIE_CONSENT_POPUP_ENABLED) ? '' : 'style="display:none;"');

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
    '<label for="%s">%s</label><span class="pp-help">%s</span><textarea id="%s" name="%s" rows="6" cols="30">%s</textarea>',
    esc_attr($control_id),
    esc_html__('Popup tracker/cookie consent message', 'mini-wp-gdpr'),
    esc_html__('Some HTML tags allowed, like <strong> and <em>', 'mini-wp-gdpr'),
    esc_attr($control_id),
    OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE,
    wp_kses_post($settings->get_string(OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE))
);
// printf(
// 	'<br />%s',
// 	esc_html__('Some HTML tags allowed, like <strong> and <em>', 'mini-wp-gdpr')
// );
echo '</p>';

echo '<p class="pp-form-row pp-checkbox">';
$props = '';
if ($settings->get_bool(OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND)) {
    $props .= ' checked';
}
$control_id = get_next_control_id();
printf(
    '<input id="%s" name="%s" type="checkbox" %s /><label for="%s">%s</label>',
    esc_attr($control_id),
    OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND,
    $props,
    esc_attr($control_id),
    esc_html__('Show consent popup even if no tracking scripts are found', 'mini-wp-gdpr')
);
echo '</p>';

echo '<p class="pp-form-row">';
$value = 0;
if (($value = $settings->get_int(OPT_SCRIPT_CONSENT_DURATION, DEFAULT_SCRIPT_CONSENT_DURATION)) <= 0) {
    $value = DEFAULT_SCRIPT_CONSENT_DURATION;
}
$control_id = get_next_control_id();
printf(
    '<label for="%s">%s</label><span class="pp-help">%s</span><input id="%s" name="%s" type="number" min="1" value="%d" />',
    esc_attr($control_id),
    esc_html__('How many days is user-consent valid for?', 'mini-wp-gdpr'),
    esc_html__('Cookie acceptance is stored in the clients\' browser using local storage.', 'mini-wp-gdpr'),
    esc_attr($control_id),
    OPT_SCRIPT_CONSENT_DURATION,
    $value
);
echo '</p>';

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
    '<label for="%s">%s</label><span class="pp-help">%s</span><select id="%s" name="%s" />',
    esc_attr($control_id),
    esc_html__('Position of the consent box', 'mini-wp-gdpr'),
    esc_html__('Put the box in a corner, or directly in the centre of the page.', 'mini-wp-gdpr'),
    esc_attr($control_id),
    OPT_CONSENT_BOX_POSITION
);

$value = $settings->get_int(OPT_CONSENT_BOX_POSITION, DEFAULT_CONSENT_BOX_POSITION);
$positions = get_consent_box_positions();
foreach ($positions as $key => $label) {
    $props = '';
    if ($key == $value) {
        $props = 'selected';
    }

    printf('<option value="%s" %s>%s</option>', esc_attr($key), $props, esc_html($label));
}
echo '</select>';
echo '</p>';

echo '</section>';

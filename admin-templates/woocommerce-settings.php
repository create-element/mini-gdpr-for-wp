<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

printf('<h2>%s</h2>', esc_html__('WooCommerce Integration', 'mini-wp-gdpr'));

echo '<p class="pp-form-row pp-checkbox">';
$control_id = get_next_control_id();
printf(
    '<input id="%s" name="%s" type="checkbox" %s  /><label for="%s">%s</label>',
    esc_attr($control_id),
    OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED,
    $settings->get_bool(OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED) ? 'checked' : '',
    esc_attr($control_id),
    esc_html__('Accept user consent when a new order is created and the Ts & Cs have been accepted. You probably want this.', 'mini-wp-gdpr')
);
echo '</p>';

echo '<p class="pp-form-row pp-checkbox">';
$control_id = get_next_control_id();
printf(
    '<input id="%s" name="%s" type="checkbox" %s class="cb-section" /><label for="%s">%s</label>',
    esc_attr($control_id),
    OPT_IS_WC_MYACCOUNT_INJECT_ENABLED,
    $settings->get_bool(OPT_IS_WC_MYACCOUNT_INJECT_ENABLED) ? 'checked' : '',
    esc_attr($control_id),
    esc_html__('Inject GDPR checkbox into My Account area, if a user has not accepted the Privacy Policy.', 'mini-wp-gdpr')
);
echo '</p>';

printf('<section %s class="mt-2">', $settings->get_bool(OPT_IS_WC_MYACCOUNT_INJECT_ENABLED) ? '' : 'style="display:none;"');

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
    '<label for="%s">%s</label><span class="pp-help">%s</span>',
    esc_attr($control_id),
    esc_html__('Which tab?', 'mini-wp-gdpr'),
    esc_html__('Choose a tab from the users\' My Account area.', 'mini-wp-gdpr')
);
printf('<select id="%s" name="%s" class="my-account-tab-chooser">', esc_attr($control_id), OPT_WHICH_WC_MYACCOUNT_ENDPOINT);
$my_account_tabs = [
    '' => '--'
];
$my_account_tabs = array_merge($my_account_tabs, wc_get_account_menu_items());
$current_value = $settings->get_string(OPT_WHICH_WC_MYACCOUNT_ENDPOINT);
foreach ($my_account_tabs as $slug => $title) {
    $props = '';
    if ($current_value == $slug) {
        $props = 'selected';
    }
    printf('<option value="%s" %s>%s</options>', esc_attr($slug), $props, esc_html($title));
}
echo '</select>';
echo '</p>';

echo '</section>';

<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

printf('<h2>%s</h2>', esc_html__('Contact Form 7 Integration', 'mini-wp-gdpr'));

$cf7_helper = get_cf7_helper();
$forms_data = [
    'labels' => [
        'installConsentButton' => esc_html__('Install Now', 'mini-wp-gdpr')
    ],
    'action' => INSTALL_CF7_CONSENT_ACTION,
    'nonce' => wp_create_nonce(INSTALL_CF7_CONSENT_ACTION),
    'forms' => $cf7_helper->get_form_metas()
];

$control_id = get_next_control_id();

printf('<div id="%s" class="pp-ajax-table" data-mwg-cf7-forms="%s">', esc_attr($control_id), esc_attr(json_encode($forms_data)));

echo pp_get_spinner_html();

echo '<table>';
echo '<thead>';
echo '<tr>';
printf('<th class="align-left">%s</th>', esc_html__('Form', 'mini-wp-gdpr'));
printf('<th class="align-centre">%s</th>', esc_html__('Privacy Consent Installed?', 'mini-wp-gdpr'));
echo '<th></th>'; // Actions
echo '</tr>';
echo '</thead>';
echo '<tbody>';
// Created in the browser.
echo '</tbody>';
echo '</table>';

echo '</div>'; // .pp-ajax-table

<?php
/**
 * Admin template: Contact Form 7 Integration settings section.
 *
 * Included by Settings_Core::render_settings_page(). The $settings variable
 * is available from the including scope.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template file included within a method; variables are scoped to the calling method, not truly global.

defined( 'ABSPATH' ) || die();

printf( '<h2>%s</h2>', esc_html__( 'Contact Form 7 Integration', 'mini-wp-gdpr' ) );

printf(
	'<p class="pp-help">%s</p>',
	esc_html__( 'Automatically adds a GDPR consent checkbox to your Contact Form 7 forms. When a user submits the form, their consent is recorded against their WordPress account.', 'mini-wp-gdpr' )
);

$cf7_helper = get_cf7_helper();
$forms_data = array(
	'labels' => array(
		'installConsentButton' => esc_html__( 'Install Now', 'mini-wp-gdpr' ),
	),
	'action' => INSTALL_CF7_CONSENT_ACTION,
	'nonce'  => wp_create_nonce( INSTALL_CF7_CONSENT_ACTION ),
	'forms'  => $cf7_helper->get_form_metas(),
);

$control_id = get_next_control_id();

printf(
	'<div id="%s" class="pp-ajax-table" data-mwg-cf7-forms="%s">',
	esc_attr( $control_id ),
	esc_attr( wp_json_encode( $forms_data ) )
);

echo pp_get_spinner_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pp_get_spinner_html() returns pre-escaped HTML.

echo '<table>';
echo '<thead>';
echo '<tr>';
printf( '<th class="align-left">%s</th>', esc_html__( 'Form', 'mini-wp-gdpr' ) );
printf( '<th class="align-centre">%s</th>', esc_html__( 'Privacy Consent Installed?', 'mini-wp-gdpr' ) );
echo '<th></th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
echo '</tbody>';
echo '</table>';

echo '</div>';

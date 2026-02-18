<?php
/**
 * Admin template: Cookie Consent settings section.
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

/** @var Settings $settings Settings controller passed in from the including method scope. */

printf( '<h2>%s</h2>', esc_html__( 'Cookie Consent', 'mini-wp-gdpr' ) );

echo '<p class="pp-form-row pp-checkbox">';
$control_id = get_next_control_id();
printf(
	'<input id="%s" name="%s" type="checkbox" %s class="cb-section"/><label for="%s">%s</label>',
	esc_attr( $control_id ),
	esc_attr( OPT_IS_COOKIE_CONSENT_POPUP_ENABLED ),
	checked( $settings->get_bool( OPT_IS_COOKIE_CONSENT_POPUP_ENABLED ), true, false ),
	esc_attr( $control_id ),
	esc_html__( 'Enable the cookie consent popup for new visitors (recommended)', 'mini-wp-gdpr' )
);
echo '</p>';

printf(
	'<section %s class="mt-2">',
	$settings->get_bool( OPT_IS_COOKIE_CONSENT_POPUP_ENABLED ) ? '' : 'style="display:none;"' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static string, not user input.
);

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
	'<label for="%s">%s</label><span class="pp-help">%s</span><textarea id="%s" name="%s" rows="6" cols="30">%s</textarea>',
	esc_attr( $control_id ),
	esc_html__( 'Popup tracker/cookie consent message', 'mini-wp-gdpr' ),
	esc_html__( 'Some HTML tags allowed, like &lt;strong&gt; and &lt;em&gt;', 'mini-wp-gdpr' ),
	esc_attr( $control_id ),
	esc_attr( OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE ),
	wp_kses_post( $settings->get_string( OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE ) )
);
echo '</p>';

echo '<p class="pp-form-row pp-checkbox">';
$control_id = get_next_control_id();
printf(
	'<input id="%s" name="%s" type="checkbox" %s /><label for="%s">%s</label>',
	esc_attr( $control_id ),
	esc_attr( OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND ),
	checked( $settings->get_bool( OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND ), true, false ),
	esc_attr( $control_id ),
	esc_html__( 'Show consent popup even if no tracking scripts are found', 'mini-wp-gdpr' )
);
echo '</p>';

echo '<p class="pp-form-row">';
$value = $settings->get_int( OPT_SCRIPT_CONSENT_DURATION, DEFAULT_SCRIPT_CONSENT_DURATION );
if ( $value <= 0 ) {
	$value = DEFAULT_SCRIPT_CONSENT_DURATION;
}
$control_id = get_next_control_id();
printf(
	'<label for="%s">%s</label><span class="pp-help">%s</span><input id="%s" name="%s" type="number" min="1" value="%d" />',
	esc_attr( $control_id ),
	esc_html__( 'How many days is user-consent valid for?', 'mini-wp-gdpr' ),
	esc_html__( 'Cookie acceptance is stored in the clients\' browser using local storage.', 'mini-wp-gdpr' ),
	esc_attr( $control_id ),
	esc_attr( OPT_SCRIPT_CONSENT_DURATION ),
	absint( $value )
);
echo '</p>';

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
	'<label for="%s">%s</label><span class="pp-help">%s</span><select id="%s" name="%s">',
	esc_attr( $control_id ),
	esc_html__( 'Position of the consent box', 'mini-wp-gdpr' ),
	esc_html__( 'Put the box in a corner, or directly in the centre of the page.', 'mini-wp-gdpr' ),
	esc_attr( $control_id ),
	esc_attr( OPT_CONSENT_BOX_POSITION )
);

$value     = $settings->get_int( OPT_CONSENT_BOX_POSITION, DEFAULT_CONSENT_BOX_POSITION );
$positions = get_consent_box_positions();
foreach ( $positions as $key => $label ) {
	printf(
		'<option value="%s" %s>%s</option>',
		esc_attr( $key ),
		selected( $key, $value, false ),
		esc_html( $label )
	);
}
echo '</select>';
echo '</p>';

printf( '<h3>%s</h3>', esc_html__( 'Button Text', 'mini-wp-gdpr' ) );

echo '<p class="pp-help">';
esc_html_e( 'Customise the text on the consent popup buttons. Leave blank to use the default.', 'mini-wp-gdpr' );
echo '</p>';

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
	'<label for="%s">%s</label><input id="%s" name="%s" type="text" value="%s" placeholder="%s" />',
	esc_attr( $control_id ),
	esc_html__( 'Accept button text', 'mini-wp-gdpr' ),
	esc_attr( $control_id ),
	esc_attr( OPT_CONSENT_ACCEPT_TEXT ),
	esc_attr( $settings->get_string( OPT_CONSENT_ACCEPT_TEXT ) ),
	esc_attr( DEF_CONSENT_ACCEPT_TEXT )
);
echo '</p>';

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
	'<label for="%s">%s</label><input id="%s" name="%s" type="text" value="%s" placeholder="%s" />',
	esc_attr( $control_id ),
	esc_html__( 'Reject button text', 'mini-wp-gdpr' ),
	esc_attr( $control_id ),
	esc_attr( OPT_CONSENT_REJECT_TEXT ),
	esc_attr( $settings->get_string( OPT_CONSENT_REJECT_TEXT ) ),
	esc_attr( DEF_CONSENT_REJECT_TEXT )
);
echo '</p>';

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
	'<label for="%s">%s</label><input id="%s" name="%s" type="text" value="%s" placeholder="%s" />',
	esc_attr( $control_id ),
	esc_html__( '"More info" button text', 'mini-wp-gdpr' ),
	esc_attr( $control_id ),
	esc_attr( OPT_CONSENT_INFO_BTN_TEXT ),
	esc_attr( $settings->get_string( OPT_CONSENT_INFO_BTN_TEXT ) ),
	esc_attr( DEF_CONSENT_INFO_BTN_TEXT )
);
echo '</p>';

echo '</section>';

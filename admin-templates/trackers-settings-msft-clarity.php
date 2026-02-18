<?php
/**
 * Admin template: Microsoft Clarity tracker settings sub-section.
 *
 * Included by trackers-settings.php. The $settings variable is available
 * from the including scope.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template file included within a method; variables are scoped to the calling method, not truly global.

defined( 'ABSPATH' ) || die();

/** @var Settings $settings Settings controller passed in from the including method scope. */

printf( '<h3><span class="dashicons dashicons-database"></span> %s</h3>', esc_html__( 'Microsoft Clarity', 'mini-wp-gdpr' ) );

printf(
	'<p class="pp-help">%s</p>',
	esc_html__( 'The Clarity SDK is delay-loaded and will only fire after the user accepts cookies. Your Project ID can be found in your Clarity dashboard under Settings \u2192 Setup.', 'mini-wp-gdpr' )
);

echo '<p class="pp-form-row pp-checkbox">';
echo pp_get_admin_checkbox_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pp_get_admin_checkbox_html() returns pre-escaped HTML.
	OPT_IS_MS_CLARITY_TRACKING_ENABLED,
	esc_html__( 'Add Microsoft Clarity', 'mini-wp-gdpr' ),
	$settings->get_bool( OPT_IS_MS_CLARITY_TRACKING_ENABLED ),
	true
);
echo '</p>';

printf(
	'<section %s class="mt-2 ml-3">',
	$settings->get_bool( OPT_IS_MS_CLARITY_TRACKING_ENABLED ) ? '' : 'style="display:none;"' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static string, not user input.
);

echo '<p class="pp-form-row">';
$control_id = get_next_control_id();
printf(
	'<label for="%s">%s</label><span class="pp-help">%s</span><input id="%s" name="%s" type="text" value="%s" />',
	esc_attr( $control_id ),
	esc_html__( 'Microsoft Clarity Project Code', 'mini-wp-gdpr' ),
	esc_html__( 'e.g. "abcdefg123"', 'mini-wp-gdpr' ),
	esc_attr( $control_id ),
	esc_attr( OPT_MS_CLARITY_ID ),
	esc_attr( $settings->get_string( OPT_MS_CLARITY_ID ) )
);
echo '</p>';

echo '</section>';

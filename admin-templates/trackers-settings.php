<?php
/**
 * Admin template: Tracking Scripts settings section.
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

printf( '<h2>%s</h2>', esc_html__( 'Tracking Scripts', 'mini-wp-gdpr' ) );

echo '<p class="pp-form-row pp-checkbox">';
$control_id = get_next_control_id();
printf(
	'<input id="%s" name="%s" type="checkbox" %s /><label for="%s">%s</label>',
	esc_attr( $control_id ),
	esc_attr( OPT_BLOCK_SCRIPTS_UNTIL_USER_CONSENTS ),
	checked( $settings->get_bool( OPT_BLOCK_SCRIPTS_UNTIL_USER_CONSENTS ), true, false ),
	esc_attr( $control_id ),
	esc_html__( 'Block tracking scripts until the user consents (if possible)?', 'mini-wp-gdpr' )
);
echo '</p>';
printf(
	'<span class="pp-help">%s</span>',
	esc_html__( 'Enabling this is more respectful of users\' privacy, but it will usually lead to your tracker(s) under-reporting site traffic.', 'mini-wp-gdpr' )
);

echo '<p class="pp-form-row pp-checkbox">';
$control_id = get_next_control_id();
printf(
	'<input id="%s" name="%s" type="checkbox" %s /><label for="%s">%s</label>',
	esc_attr( $control_id ),
	esc_attr( OPT_IS_ADMIN_TRACKING_ENABLED ),
	checked( $settings->get_bool( OPT_IS_ADMIN_TRACKING_ENABLED ), true, false ),
	esc_attr( $control_id ),
	esc_html__( 'Allow tracking scripts to run even when logged-in as administrator?', 'mini-wp-gdpr' )
);
echo '</p>';
printf( '<span class="pp-help">%s</span>', esc_html__( 'By default, tracking scripts won\'t be added for users who are logged-in as administrator.', 'mini-wp-gdpr' ) );

require __DIR__ . '/trackers-settings-facebook.php';
require __DIR__ . '/trackers-settings-google.php';
require __DIR__ . '/trackers-settings-msft-clarity.php';

<?php
/**
 * Admin template: Tracking Scripts settings section.
 *
 * Included by Settings::render_settings_page(). The $settings variable
 * is available from the including scope.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template file included within a method; variables are scoped to the calling method, not truly global.

defined( 'ABSPATH' ) || die();

/** @var Settings $settings Settings controller passed in from the including method scope. */

printf( '<h2>%s</h2>', esc_html__( 'Tracking Scripts', 'mini-wp-gdpr' ) );

printf(
	'<p class="mwg-help">%s</p>',
	esc_html__( 'Configure which analytics and tracking scripts are added to your site. Scripts are only injected after the user gives explicit consent.', 'mini-wp-gdpr' )
);

echo '<p class="mwg-form-row mwg-checkbox">';
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
printf( '<span class="mwg-help">%s</span>', esc_html__( 'By default, tracking scripts won\'t be added for users who are logged-in as administrator.', 'mini-wp-gdpr' ) );

require __DIR__ . '/trackers-settings-facebook.php';
require __DIR__ . '/trackers-settings-google.php';
require __DIR__ . '/trackers-settings-msft-clarity.php';

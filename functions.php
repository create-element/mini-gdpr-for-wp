<?php
/**
 * Public API functions for Mini WP GDPR.
 *
 * These functions form the public-facing developer API for the plugin.
 * They are deliberately in the global namespace so that theme and plugin
 * authors can call them without importing the Mini_Wp_Gdpr namespace.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || die();

/**
 * Returns whether the given user has accepted the privacy policy.
 *
 * @since 1.0.0
 *
 * @param int $user_id WordPress user ID. Defaults to the current user (0).
 * @return bool True if the user has accepted the privacy policy.
 */
function mwg_has_user_accepted_privacy_policy( int $user_id = 0 ) {
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}

	$user_controller = Mini_Wp_Gdpr\get_user_controller();

	return $user_controller->has_user_accepted_gdpr( $user_id );
}

/**
 * Returns the date/time when the given user accepted the privacy policy.
 *
 * @since 1.0.0
 *
 * @param int    $user_id WordPress user ID. Defaults to the current user (0).
 * @param string $format  PHP date format string. Defaults to the site's date format option.
 * @return string|false Formatted date string, or false if the user has not accepted.
 */
function mwg_when_did_user_accept_privacy_policy( int $user_id = 0, string $format = '' ) {
	if ( $user_id <= 0 ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $format ) ) {
		$format = get_option( 'date_format', '' );
	}

	$user_controller = Mini_Wp_Gdpr\get_user_controller();

	return $user_controller->when_did_user_accept_gdpr( $user_id, $format );
}

/**
 * Renders the GDPR acceptance checkbox form for the current user.
 *
 * Outputs the mini accept-terms form template directly. Use inside themes or
 * other plugins to show the GDPR checkbox for the currently logged-in user.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mwg_get_mini_accept_terms_form_for_current_user() {
	include PP_MWG_PUBLIC_TEMPLATES_DIR . 'mini-accept-form.php';
}

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

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Public API; mwg_ prefix is intentional and documented. WPCS rejects prefixes shorter than 4 chars so the rule is disabled for this file.

/**
 * Returns whether the given user has accepted the privacy policy.
 *
 * @since 1.0.0
 *
 * @param int $user_id WordPress user ID. Defaults to the current user (0).
 * @return bool True if the user has accepted the privacy policy.
 *
 * @example
 * // Check consent for the currently logged-in user.
 * if ( mwg_has_user_accepted_privacy_policy() ) {
 *     echo 'Thank you for accepting our privacy policy.';
 * }
 *
 * @example
 * // Check consent for a specific user (e.g. in a WP-CLI script or admin notice).
 * $user_id = 42;
 * if ( mwg_has_user_accepted_privacy_policy( $user_id ) ) {
 *     // User has consented — safe to send marketing email.
 * }
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
 * @return string|null Formatted date string, or null if the user has not accepted.
 *
 * @example
 * // Display the acceptance date for the current user using the site's date format.
 * $date = mwg_when_did_user_accept_privacy_policy();
 * if ( $date ) {
 *     printf( 'You accepted our privacy policy on %s.', esc_html( $date ) );
 * }
 *
 * @example
 * // Display the acceptance date for a specific user with a custom format.
 * $date = mwg_when_did_user_accept_privacy_policy( get_current_user_id(), 'F j, Y \a\t g:i a' );
 * if ( $date ) {
 *     // e.g. "February 18, 2026 at 10:30 am"
 *     echo esc_html( $date );
 * }
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
 * The checkbox submits via JavaScript/AJAX — no page reload required.
 *
 * @since 1.0.0
 *
 * @return void
 *
 * @example
 * // Embed the consent checkbox inside a WooCommerce My Account template override.
 * if ( is_user_logged_in() && ! mwg_has_user_accepted_privacy_policy() ) {
 *     mwg_get_mini_accept_terms_form_for_current_user();
 * }
 *
 * @example
 * // Add the form to a WordPress shortcode.
 * add_shortcode( 'gdpr_consent_form', function () {
 *     if ( ! is_user_logged_in() ) {
 *         return '';
 *     }
 *     ob_start();
 *     mwg_get_mini_accept_terms_form_for_current_user();
 *     return ob_get_clean();
 * } );
 */
function mwg_get_mini_accept_terms_form_for_current_user() {
	include PP_MWG_PUBLIC_TEMPLATES_DIR . 'mini-accept-form.php';
}

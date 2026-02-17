<?php
/**
 * Private (internal) helper functions for Mini WP GDPR.
 *
 * These functions are for use within the plugin only and are not part of
 * the public API. All functions are namespaced under Mini_Wp_Gdpr.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Returns the global User_Controller instance.
 *
 * @return User_Controller
 */
function get_user_controller() {
	global $pp_mwg_plugin;
	return $pp_mwg_plugin->get_user_controller();
}

/**
 * Returns the global Settings controller instance.
 *
 * @return Settings
 */
function get_settings_controller() {
	global $pp_mwg_plugin;
	return $pp_mwg_plugin->get_settings_controller();
}

/**
 * Returns the global Script_Blocker instance.
 *
 * @return Script_Blocker
 */
function get_script_blocker() {
	global $pp_mwg_plugin;
	return $pp_mwg_plugin->get_script_blocker();
}

/**
 * Returns the global CF7_Helper instance.
 *
 * @return CF7_Helper
 */
function get_cf7_helper() {
	global $pp_mwg_plugin;
	return $pp_mwg_plugin->get_cf7_helper();
}

/**
 * Returns whether Contact Form 7 is installed and active.
 *
 * @return bool
 */
function is_cf7_installed() {
	$cf7_helper = get_cf7_helper();
	return $cf7_helper->is_cf7_installed();
}

/**
 * Returns a shared DateTime object representing the current time in the site timezone.
 *
 * The result is cached in a global so multiple calls within the same request
 * return the same instant.
 *
 * @return \DateTime
 */
function get_date_time_now() {
	global $pp_mwg_gdrp_now;

	if ( is_null( $pp_mwg_gdrp_now ) ) {
		$pp_mwg_gdrp_now = new \DateTime( 'now', wp_timezone() );
	}

	return $pp_mwg_gdrp_now;
}

/**
 * Returns the current date/time as a human-readable string (Y-m-d H:i:s T).
 *
 * Example: "2026-02-17 07:00:00 GMT"
 *
 * @return string|null Formatted date string, or null if get_date_time_now() fails.
 */
function get_date_time_now_h() {
	global $pp_mwg_gdrp_now_h;

	// phpcs:disable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments -- Intentional SESE guard pattern.
	if ( ! is_null( $pp_mwg_gdrp_now_h ) ) {
		// Already cached — return early.
	} elseif ( empty( ( $now = get_date_time_now() ) ) ) {
		// Could not retrieve current DateTime.
	} else {
		$pp_mwg_gdrp_now_h = $now->format( 'Y-m-d H:i:s T' );
	}
	// phpcs:enable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments

	return $pp_mwg_gdrp_now_h;
}

/**
 * Returns whether the Mini GDPR plugin is enabled.
 *
 * The plugin is considered enabled when the site has a Privacy Policy URL
 * configured in Settings → Privacy.
 *
 * @return bool
 */
function is_mini_gdpr_enabled() {
	global $pp_mwg_is_mini_gdpr_enabled;

	if ( is_null( $pp_mwg_is_mini_gdpr_enabled ) ) {
		$pp_mwg_is_mini_gdpr_enabled = false;
	}

	// phpcs:disable Generic.CodeAnalysis.EmptyStatement -- Intentional SESE guard pattern.
	if ( empty( get_privacy_policy_url() ) ) {
		// No privacy policy URL — plugin remains disabled.
	} else {
		$pp_mwg_is_mini_gdpr_enabled = true;
	}
	// phpcs:enable Generic.CodeAnalysis.EmptyStatement

	return $pp_mwg_is_mini_gdpr_enabled;
}

/**
 * Enqueues all front-end CSS and JS assets for the plugin (once per page load).
 *
 * @return void
 */
function enqueue_frontend_assets() {
	global $pp_mwg_is_mini_gdpr_frontend_enqueued;

	if ( is_null( $pp_mwg_is_mini_gdpr_frontend_enqueued ) ) {
		wp_enqueue_style( 'mini-wp-gdpr', PP_MWG_ASSETS_URL . 'mini-gdpr.css', array(), PP_MWG_VERSION, 'all' );

		wp_enqueue_script( 'mini-wp-gdpr', PP_MWG_ASSETS_URL . 'mini-gdpr.js', array( 'jquery' ), PP_MWG_VERSION, true );

		$params = array(
			'termsNotAccepted' => __( 'Please accept the GDPR terms before proceeding.', 'mini-wp-gdpr' ),
			'miniFormPlease'   => get_accept_gdpr_checkbox_text(),
		);

		if ( is_user_logged_in() ) {
			$params['acceptAction'] = ACCEPT_GDPR_ACTION;
			$params['acceptNonce']  = wp_create_nonce( ACCEPT_GDPR_ACTION );
			$params['ajaxUrl']      = admin_url( 'admin-ajax.php' );
		}

		wp_localize_script( 'mini-wp-gdpr', 'miniWpGdpr', $params );

		$pp_mwg_is_mini_gdpr_frontend_enqueued = true;
	}
}

/**
 * Returns the thank-you text displayed after a user accepts GDPR terms.
 *
 * @return string
 */
function get_thankyou_text() {
	return __( "Thanks. That's the official GDPR stuff sorted.", 'mini-wp-gdpr' );
}

/**
 * Returns the full outer HTML for the GDPR acceptance checkbox label.
 *
 * @return string
 */
function get_accept_gdpr_checkbox_outer_html() {
	$control_name = ACCEPT_GDPR_FORM_CONTROL_NAME;

	$props = '';
	if ( mwg_has_user_accepted_privacy_policy() ) {
		$props .= ' checked';
	}

	$html  = '<label class="checkbox">';
	$html .= sprintf(
		'<input class="input-checkbox mini-gdpr-checkbox" name="%s" id="%s" type="checkbox" value="1" %s /><span>%s</span>',
		esc_attr( $control_name ),
		esc_attr( $control_name ),
		$props,
		get_accept_gdpr_checkbox_text()
	);
	$html .= '</label>';

	return $html;
}

/**
 * Returns the label text for the GDPR acceptance checkbox.
 *
 * @return string
 */
function get_accept_gdpr_checkbox_text() {
	return sprintf(
		/* translators: 1: site name, 2: privacy policy URL */
		__( 'I agree to the handling of my personal data by %1$s, as per the <a href="%2$s">Privacy Policy</a>', 'mini-wp-gdpr' ),
		esc_html( get_bloginfo( 'name' ) ),
		esc_url( get_privacy_policy_url() )
	);
}

/**
 * Returns whether the GDPR checkbox was accepted in the current POST request.
 *
 * Checks the plugin consent checkbox, the WooCommerce terms checkbox, and the
 * Contact Form 7 consent tag.
 *
 * @return bool
 */
function is_gdpr_accepted_in_post_data() {
	$control_names = array( ACCEPT_GDPR_FORM_CONTROL_NAME, 'terms' );

	$is_gdpr_accepted = false;

	// phpcs:disable WordPress.Security.NonceVerification.Missing, Generic.CodeAnalysis.EmptyStatement -- Nonce verified by callers; SESE guard pattern.
	foreach ( $control_names as $control_name ) {
		if ( array_key_exists( $control_name, $_POST ) && filter_var( wp_unslash( $_POST[ $control_name ] ), FILTER_VALIDATE_BOOLEAN ) ) {
			$is_gdpr_accepted = true;
			break;
		}
	}

	// Check for CF7 consent tag.
	if ( $is_gdpr_accepted ) {
		// Already accepted — nothing more to check.
	} elseif ( ! array_key_exists( CF7_CONSENT_TAG_NAME, $_POST ) ) {
		// CF7 consent tag not present.
	} elseif ( ! is_array( $_POST[ CF7_CONSENT_TAG_NAME ] ) ) {
		// CF7 consent tag value is not an array.
	} else {
		$is_gdpr_accepted = ( 1 === count( $_POST[ CF7_CONSENT_TAG_NAME ] ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing, Generic.CodeAnalysis.EmptyStatement

	return $is_gdpr_accepted;
}

/**
 * Returns the combined unique list of all script-block domains (blacklist + whitelist).
 *
 * @return string[]
 */
function get_all_script_block_domains() {
	return array_unique( array_merge( get_script_block_lists_blacklist(), get_script_block_lists_whitelist() ) );
}

/**
 * Returns a regex pattern that matches any URL containing the given domain.
 *
 * @param string $domain Domain name (e.g. "googletagmanager.com").
 * @return string Regex pattern.
 */
function get_script_block_regex_from_domain( string $domain ) {
	return sprintf( '/%s/', str_replace( '.', '\\.', $domain ) );
}

/**
 * Returns whether the script blocker feature is currently enabled.
 *
 * Requires: at least one domain on the block lists, and the cookie consent
 * popup option enabled.
 *
 * @return bool
 */
function is_script_blocker_enabled() {
	$is_enabled = false;

	$settings = get_settings_controller();

	// phpcs:disable Generic.CodeAnalysis.EmptyStatement -- Intentional SESE guard pattern.
	if ( empty( get_script_block_lists_blacklist() ) && empty( get_script_block_lists_whitelist() ) ) {
		// No domains configured — script blocker has nothing to block.
	} elseif ( ! $settings->get_bool( OPT_IS_COOKIE_CONSENT_POPUP_ENABLED ) ) {
		// Cookie consent popup is disabled.
	} else {
		$is_enabled = true;
	}
	// phpcs:enable Generic.CodeAnalysis.EmptyStatement

	return $is_enabled;
}

/**
 * Returns the available consent box positions as an array indexed by position integer.
 *
 * @return string[]
 */
function get_consent_box_positions() {
	return array(
		0 => __( 'Bottom Left', 'mini-wp-gdpr' ),
		1 => __( 'Bottom Centre', 'mini-wp-gdpr' ),
		2 => __( 'Bottom Right', 'mini-wp-gdpr' ),
		3 => __( 'Middle Left', 'mini-wp-gdpr' ),
		4 => __( 'Middle Centre', 'mini-wp-gdpr' ),
		5 => __( 'Middle Right', 'mini-wp-gdpr' ),
		6 => __( 'Top Left', 'mini-wp-gdpr' ),
		7 => __( 'Top Centre', 'mini-wp-gdpr' ),
		8 => __( 'Top Right', 'mini-wp-gdpr' ),
	);
}

/**
 * Returns the CSS modifier classes for the given consent box position.
 *
 * @param int $position Position index (0-8). Pass -1 to read from settings.
 * @return string[] Array of CSS class names.
 */
function get_consent_box_styles( int $position = -1 ) {
	$styles = null;

	if ( $position < 0 ) {
		$settings = get_settings_controller();
		$position = $settings->get_int( OPT_CONSENT_BOX_POSITION, DEFAULT_CONSENT_BOX_POSITION );
	}

	switch ( $position ) {
		case 6:
			$styles = array( 'mgw-lft', 'mgw-top' );
			break;

		case 7:
			$styles = array( 'mgw-hcn', 'mgw-top' );
			break;

		case 8:
			$styles = array( 'mgw-rgt', 'mgw-top' );
			break;

		case 3:
			$styles = array( 'mgw-lft', 'mgw-vcn' );
			break;

		case 4:
			$styles = array( 'mgw-hcn', 'mgw-vcn' );
			break;

		case 5:
			$styles = array( 'mgw-rgt', 'mgw-vcn' );
			break;

		case 0:
			$styles = array( 'mgw-lft', 'mgw-btm' );
			break;

		case 1:
			$styles = array( 'mgw-hcn', 'mgw-btm' );
			break;

		case 2:
			$styles = array( 'mgw-rgt', 'mgw-btm' );
			break;
	}

	if ( ! is_array( $styles ) ) {
		$styles = array( 'mgw-rgt', 'mgw-vcn' );
	}

	return $styles;
}

/**
 * Returns whether an external Google Analytics injector plugin is currently active.
 *
 * @return bool
 */
function is_external_ga_injector_plugin_installed() {
	$is_installed = false;

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins = EXTERNAL_GA_TRACKER_PLUGINS;
	foreach ( $plugins as $plugin ) {
		if ( is_plugin_active( $plugin ) ) {
			$is_installed = true;
			break;
		}
	}

	return $is_installed;
}

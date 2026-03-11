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
 * @since 2.0.0
 * @return User_Controller
 */
function get_user_controller() {
	global $pp_mwg_plugin;
	return $pp_mwg_plugin->get_user_controller();
}

/**
 * Returns the global Settings controller instance.
 *
 * @since 2.0.0
 * @return Settings
 */
function get_settings_controller() {
	global $pp_mwg_plugin;
	return $pp_mwg_plugin->get_settings_controller();
}

/**
 * Returns the global Script_Blocker instance.
 *
 * @since 2.0.0
 * @return Script_Blocker
 */
function get_script_blocker() {
	global $pp_mwg_plugin;
	return $pp_mwg_plugin->get_script_blocker();
}

/**
 * Returns the global CF7_Helper instance.
 *
 * @since 2.0.0
 * @return CF7_Helper
 */
function get_cf7_helper() {
	global $pp_mwg_plugin;
	return $pp_mwg_plugin->get_cf7_helper();
}

/**
 * Returns whether Contact Form 7 is installed and active.
 *
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * Loads minified assets in production and source assets when SCRIPT_DEBUG is
 * enabled, following the standard WordPress plugin convention.
 *
 * @since 1.0.0
 * @return void
 */
function enqueue_frontend_assets() {
	global $pp_mwg_is_mini_gdpr_frontend_enqueued;

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	if ( is_null( $pp_mwg_is_mini_gdpr_frontend_enqueued ) ) {
		wp_enqueue_style( 'mini-wp-gdpr', PP_MWG_ASSETS_URL . 'mini-gdpr.css', array(), PP_MWG_VERSION, 'all' );

		wp_enqueue_script( 'mini-wp-gdpr', PP_MWG_ASSETS_URL . "mini-gdpr$suffix.js", array(), PP_MWG_VERSION, true );

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
 * @since 1.0.0
 * @return string
 */
function get_thankyou_text() {
	return __( "Thanks. That's the official GDPR stuff sorted.", 'mini-wp-gdpr' );
}

/**
 * Returns the full outer HTML for the GDPR acceptance checkbox label.
 *
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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
 * Returns a regex pattern that matches any URL containing the given domain.
 *
 * @since 1.0.0
 * @param string $domain Domain name (e.g. "googletagmanager.com").
 * @return string Regex pattern.
 */
function get_script_block_regex_from_domain( string $domain ) {
	return sprintf( '/%s/', str_replace( '.', '\\.', $domain ) );
}

/**
 * Returns the available consent box positions as an array indexed by position integer.
 *
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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

// ---------------------------------------------------------------------------
// Admin UI helpers (moved from includes/functions-admin-ui.php)
// ---------------------------------------------------------------------------

/**
 * Generate a unique HTML control ID for the current request.
 *
 * Increments a global counter on each call and returns an ID string such as
 * 'ppctx1', 'ppctx2', etc. The prefix matches the original pp-core.php
 * implementation so existing admin CSS and JS selectors continue to work.
 *
 * @since 2.0.0
 * @return string Unique control ID.
 */
function get_next_control_id() {
	global $pp_mwg_control_index;

	if ( is_null( $pp_mwg_control_index ) ) {
		$pp_mwg_control_index = 1;
	}

	$control_id = 'ppctx' . $pp_mwg_control_index;

	++$pp_mwg_control_index;

	return $control_id;
}

/**
 * Check whether WooCommerce is active.
 *
 * @since 2.0.0
 * @return bool
 */
function is_woocommerce_available() {
	return function_exists( 'WC' );
}

/**
 * Die with no output if the nonce or capability check fails.
 *
 * Used in AJAX handlers to enforce nonce verification and capability checks
 * before processing any data.
 *
 * @since 2.0.0
 * @param string $action       Nonce action string.
 * @param string $required_cap WordPress capability (e.g. 'manage_options').
 * @param string $nonce_field  $_POST key that holds the nonce value.
 * @return void
 */
function die_if_bad_nonce_or_cap( string $action, string $required_cap, string $nonce_field = 'nonce' ) {
	$nonce_present = array_key_exists( $nonce_field, $_POST );
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce value must not be altered before verification.
	$nonce_valid = $nonce_present && wp_verify_nonce( wp_unslash( $_POST[ $nonce_field ] ), $action );
	$cap_valid   = empty( $required_cap ) || ( is_user_logged_in() && current_user_can( $required_cap ) );

	if ( ! $nonce_valid || ! $cap_valid ) {
		die();
	}
}

/**
 * Check whether the current request is within the AJAX rate limit for an action.
 *
 * Counts per-user requests using a WordPress transient. Returns true (proceed)
 * when the request count is below $max_requests, and false (block) once the
 * limit is reached. Increments the counter only on allowed requests so the
 * window does not reset on blocked calls.
 *
 * Transient keys are scoped per user ID and action key, so limits are
 * independent across users and across different AJAX actions.
 *
 * @since 2.0.0
 * @param string $action_key     Short identifier for the action (alphanumeric + hyphens).
 * @param int    $max_requests   Maximum requests allowed within the window.
 * @param int    $window_seconds Duration of the rate-limit window in seconds.
 * @return bool True when within limit (OK to proceed), false when rate-limited.
 */
function is_within_ajax_rate_limit( string $action_key, int $max_requests, int $window_seconds ): bool {
	$transient_key = 'mwg_rl_' . get_current_user_id() . '_' . $action_key;
	$current_count = (int) get_transient( $transient_key );
	$within_limit  = $current_count < $max_requests;

	if ( $within_limit ) {
		set_transient( $transient_key, $current_count + 1, $window_seconds );
	}

	return $within_limit;
}

/**
 * Enqueue shared admin CSS for the plugin settings pages.
 *
 * Loads the plugin-specific admin stylesheet (assets/mwg-admin.css).
 *
 * @since 2.0.0
 * @return void
 */
function enqueue_admin_assets() {
	global $pp_mwg_admin_assets_enqueued;

	if ( is_null( $pp_mwg_admin_assets_enqueued ) ) {
		$pp_mwg_admin_assets_enqueued = false;
	}

	$should_enqueue = is_admin() && ! wp_doing_ajax() && ! $pp_mwg_admin_assets_enqueued;

	if ( $should_enqueue ) {
		wp_enqueue_style(
			'mwg-admin-base',
			PP_MWG_ASSETS_URL . 'mwg-admin.css',
			[],
			PP_MWG_VERSION
		);

		$pp_mwg_admin_assets_enqueued = true;
	}
}

/**
 * Return a spinner image element.
 *
 * Uses the plugin's own assets/spinner.svg.
 *
 * @since 2.0.0
 * @param bool $is_visible Whether the spinner should be visible on load.
 * @return string HTML <img> element.
 */
function get_spinner_html( bool $is_visible = false ) {
	$style = $is_visible ? '' : 'style="display:none;"';

	return sprintf(
		'<div class="mwg-spinner" %s><img src="%s" alt="%s" /></div>',
		$style,
		esc_url( PP_MWG_ASSETS_URL . 'spinner.svg' ),
		esc_attr__( 'Loading', 'mini-wp-gdpr' )
	);
}

/**
 * Return a button element paired with a loading spinner.
 *
 * @since 2.0.0
 * @param string $label         Visible button label text.
 * @param string $button_classes Additional CSS classes for the button.
 * @param string $button_props   Additional HTML attributes for the button.
 * @return string HTML markup for the button + spinner.
 */
function get_button_with_spinner_html( string $label, string $button_classes = '', string $button_props = '' ) {
	$classes   = array_filter( explode( ' ', $button_classes ) );
	$classes[] = 'button';

	return sprintf(
		'<span class="mwg-button-with-spinner"><button %s class="%s">%s</button><img src="%s" style="display:none;" alt="%s" class="mwg-spinner-img" /></span>',
		$button_props,
		esc_attr( implode( ' ', $classes ) ),
		esc_html( $label ),
		esc_url( PP_MWG_ASSETS_URL . 'spinner.svg' ),
		esc_attr__( 'Loading', 'mini-wp-gdpr' )
	);
}

/**
 * Return a settings page header link element.
 *
 * Renders a help/support link next to the page <h1>.
 *
 * @since 2.0.0
 * @param string $support_url         URL for the help/support link.
 * @param string $support_link_tooltip Tooltip text for the link.
 * @return string HTML anchor element, or empty string if no URL provided.
 */
function get_settings_header_html( string $support_url = '', string $support_link_tooltip = '' ) {
	$html = '';

	if ( ! empty( $support_url ) ) {
		if ( empty( $support_link_tooltip ) ) {
			$support_link_tooltip = __( 'Online Help', 'mini-wp-gdpr' );
		}

		$html = sprintf(
			'<a href="%s" title="%s" class="mwg-help-link" target="_blank">%s</a>',
			esc_url( $support_url ),
			esc_attr( $support_link_tooltip ),
			esc_html( $support_link_tooltip )
		);
	}

	return $html;
}

/**
 * Return an admin checkbox input with an associated label.
 *
 * @since 2.0.0
 * @param string $field_name          The input name attribute (also used as the option key).
 * @param string $label               Visible label text.
 * @param bool   $is_checked          Whether the checkbox should be pre-checked.
 * @param bool   $has_following_section Whether a collapsible section follows this checkbox.
 * @param string $additional_classes  Extra CSS classes for the input.
 * @return string HTML checkbox + label markup.
 */
function get_admin_checkbox_html( string $field_name, string $label, bool $is_checked = false, bool $has_following_section = false, string $additional_classes = '' ) {
	$control_id = get_next_control_id();

	$props = '';
	if ( $is_checked ) {
		$props .= ' checked';
	}

	$classes = array_filter( explode( ' ', $additional_classes ) );
	if ( $has_following_section ) {
		$classes[] = 'cb-section';
	}

	if ( ! empty( $classes ) ) {
		$props .= sprintf( ' class="%s"', trim( esc_attr( implode( ' ', $classes ) ) ) );
	}

	$html = sprintf(
		'<input id="%s" name="%s" type="checkbox" %s/>',
		esc_attr( $control_id ),
		esc_attr( $field_name ),
		$props
	);

	if ( ! empty( $label ) ) {
		$html .= sprintf(
			'<label for="%s">%s</label>',
			esc_attr( $control_id ),
			esc_html( $label )
		);
	}

	return $html;
}

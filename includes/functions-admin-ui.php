<?php

/**
 * Admin UI helper functions.
 *
 * Plugin-native replacements for the pp-core.php admin UI utilities removed
 * in M3. All functions retain their original names and signatures so calling
 * code in templates and class files requires no changes.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

// ---------------------------------------------------------------------------
// General helpers
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

// ---------------------------------------------------------------------------
// Security
// ---------------------------------------------------------------------------

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
function pp_die_if_bad_nonce_or_cap( string $action, string $required_cap, string $nonce_field = 'nonce' ) {
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
function pp_is_within_ajax_rate_limit( string $action_key, int $max_requests, int $window_seconds ): bool {
	$transient_key = 'mwg_rl_' . get_current_user_id() . '_' . $action_key;
	$current_count = (int) get_transient( $transient_key );
	$within_limit  = $current_count < $max_requests;

	if ( $within_limit ) {
		set_transient( $transient_key, $current_count + 1, $window_seconds );
	}

	return $within_limit;
}

// ---------------------------------------------------------------------------
// Asset enqueueing
// ---------------------------------------------------------------------------

/**
 * Enqueue shared admin CSS for the plugin settings pages.
 *
 * Loads the plugin-specific admin stylesheet (assets/mwg-admin.css).
 * This replaced the pp-assets/pp-admin.css reference removed in M3.
 *
 * @since 2.0.0
 * @return void
 */
function pp_enqueue_admin_assets() {
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

// ---------------------------------------------------------------------------
// HTML component helpers
// ---------------------------------------------------------------------------

/**
 * Return a spinner image element.
 *
 * Uses the plugin's own assets/spinner.svg.
 *
 * @since 2.0.0
 * @param bool $is_visible Whether the spinner should be visible on load.
 * @return string HTML <img> element.
 */
function pp_get_spinner_html( bool $is_visible = false ) {
	$style = $is_visible ? '' : 'style="display:none;"';

	return sprintf(
		'<div class="pp-spinner" %s><img src="%s" alt="%s" /></div>',
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
function pp_get_button_with_spinner_html( string $label, string $button_classes = '', string $button_props = '' ) {
	$classes   = array_filter( explode( ' ', $button_classes ) );
	$classes[] = 'button';

	return sprintf(
		'<span class="pp-button-with-spinner"><button %s class="%s">%s</button><img src="%s" style="display:none;" alt="%s" class="pp-spinner-img" /></span>',
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
 * Renders a help/support link next to the page <h1>. The PP logo image has
 * been removed as part of the pp-core.php dependency removal in M3.
 *
 * @since 2.0.0
 * @param string $support_url         URL for the help/support link.
 * @param string $support_link_tooltip Tooltip text for the link.
 * @return string HTML anchor element, or empty string if no URL provided.
 */
function pp_get_header_logo_html( string $support_url = '', string $support_link_tooltip = '' ) {
	$html = '';

	if ( ! empty( $support_url ) ) {
		if ( empty( $support_link_tooltip ) ) {
			$support_link_tooltip = __( 'Online Help', 'mini-wp-gdpr' );
		}

		$html = sprintf(
			'<a href="%s" title="%s" class="pp-help-link" target="_blank">%s</a>',
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
function pp_get_admin_checkbox_html( string $field_name, string $label, bool $is_checked = false, bool $has_following_section = false, string $additional_classes = '' ) {
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

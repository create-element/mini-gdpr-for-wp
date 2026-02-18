<?php
/**
 * Public template: GDPR mini-accept form.
 *
 * Outputs the consent checkbox for the current user. Included by
 * mwg_get_mini_accept_terms_form_for_current_user() and also used via the
 * WooCommerce My Account injection in Public_Hooks::inject_into_wc_myaccount_endpoint().
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

enqueue_frontend_assets();

echo '<p class="mini-gdpr-form">';
echo get_accept_gdpr_checkbox_outer_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_accept_gdpr_checkbox_outer_html() returns pre-escaped HTML.
echo '</p>';

<?php

/**
 * Google Analytics tracker integration.
 *
 * Registers the GA script handle as blockable, provides tracker metadata,
 * enqueues the GA script after consent, and outputs Google Consent Mode v2
 * default signals in the page <head> when Consent Mode is enabled.
 *
 * Google Consent Mode v2 workflow:
 *   1. wp_head (priority 1): dataLayer + gtag stub + gtag('consent','default',…) output.
 *   2. User accepts consent: JS calls gtag('consent','update',…) before deferred GA loads.
 *   3. GA script loads via insertBlockedScripts() and picks up the queued granted state.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Output Google Consent Mode v2 default signals in <head> at the earliest
 * possible priority.
 *
 * Runs only when GA tracking is enabled AND Consent Mode is enabled in plugin
 * settings. Initialises window.dataLayer and the gtag() stub (so both are
 * available before gtag.js loads), then sets all consent categories to 'denied'
 * with a 500 ms wait_for_update window.
 *
 * The phpcs:ignore below suppresses the OutputNotEscaped warning for the
 * wp_json_encode() return value, which is safe: the array contains only
 * plugin-controlled string constants and a single integer literal — no user
 * input reaches this output.
 *
 * @since 2.0.0
 * @return void
 */
add_action(
	'wp_head',
	function () {
		$settings            = get_settings_controller();
		$is_tracking_enabled = $settings->get_bool( OPT_IS_GA_TRACKING_ENABLED );
		$is_consent_mode     = $settings->get_bool( OPT_GA_CONSENT_MODE_ENABLED );

		if ( $is_tracking_enabled && $is_consent_mode ) {
			$defaults = wp_json_encode(
				array(
					'analytics_storage'  => 'denied',
					'ad_storage'         => 'denied',
					'ad_user_data'       => 'denied',
					'ad_personalization' => 'denied',
					'wait_for_update'    => 500,
				)
			);

			printf(
				"<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag(\"consent\",\"default\",%s);</script>\n",
				$defaults // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output; values are plugin-controlled constants only.
			);
		}
	},
	1
);

add_filter(
	'mwg_blockable_script_handles',
	function ( $handles ) {
		$handles[] = GA_SCRIPT_HANDLE;

		return $handles;
	}
);

add_filter(
	'mwg_tracker_' . GA_SCRIPT_HANDLE,
	function () {
		return [
			'pattern'     => '/googletagmanager\\.com/',
			'field'       => 'src',
			'description' => __( 'Google Analytics', 'mini-wp-gdpr' ),
		];
	}
);

add_action(
	'mwg_inject_tracker_' . GA_SCRIPT_HANDLE,
	function () {
		$settings = get_settings_controller();

		$is_tracking_enabled = $settings->get_bool( OPT_IS_GA_TRACKING_ENABLED );

		if ( ! $is_tracking_enabled ) {
			// ...
		} elseif ( empty( ( $tracker_code = $settings->get_string( OPT_GA_TRACKING_CODE ) ) ) ) {
			error_log( __FUNCTION__ . ' Missing Google Analytics tracker code' );
		} elseif ( ! preg_match( '/^(G|UA|YT|MO)-[a-zA-Z0-9-]+$/', $tracker_code ) ) {
			error_log( __FUNCTION__ . ' Invalid Google Analytics tracker code' );
		} else {
			wp_enqueue_script( GA_SCRIPT_HANDLE, 'https://www.googletagmanager.com/gtag/js?id=' . $tracker_code, null, false );

			wp_add_inline_script(
				GA_SCRIPT_HANDLE,
				sprintf(
					'window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag(\'js\', new Date());
gtag(\'config\', \'%s\');
',
					wp_strip_all_tags( $tracker_code )
				),
				'after'
			);
		}
	}
);

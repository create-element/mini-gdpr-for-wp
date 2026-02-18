<?php

/**
 * Google Analytics tracker integration.
 *
 * Registers the GA script handle as blockable, provides tracker metadata,
 * outputs a minimal gtag stub in the page <head>, and queues the gtag config
 * calls so gtag.js can replay them after consent.
 *
 * Google Analytics delay-loading workflow:
 *   1. wp_head (priority 1): dataLayer + gtag stub output unconditionally when GA
 *      is enabled. If Consent Mode v2 is also enabled, the stub includes the
 *      gtag('consent','default',{...denied}) call so all consent categories are
 *      denied until the user accepts.
 *   2. mwg_inject_tracker_: registers an empty-src inline script that queues
 *      gtag('js', new Date()) and gtag('config', 'ID') in dataLayer. These calls
 *      are NOT sent to Google — they sit in the queue until gtag.js loads.
 *   3. User accepts consent: JS calls gtag('consent','update',{...granted}) then
 *      loadGoogleAnalytics(), which dynamically loads gtag.js. On load, gtag.js
 *      processes the full dataLayer queue (consent default → js → config → update)
 *      and initialises tracking in the granted state.
 *   4. Returning visitor (already consented): init() calls loadGoogleAnalytics()
 *      directly, loading gtag.js which replays the queued calls.
 *
 * This ensures gtag.js never loads before explicit user consent (GDPR-compliant)
 * regardless of whether the 'block scripts until consent' option is enabled.
 * The gtag stub itself is harmless: it creates a local dataLayer queue but sends
 * no data to Google without gtag.js.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Output the dataLayer init + gtag stub in <head> at the earliest possible priority.
 *
 * Runs whenever GA tracking is enabled. The stub:
 *   - Initialises window.dataLayer (idempotent: window.dataLayer || []).
 *   - Defines the gtag() function so any gtag() calls before gtag.js loads
 *     are queued in dataLayer for later replay.
 *
 * When Google Consent Mode v2 is also enabled, appends a
 * gtag('consent','default',{...denied}) call to the stub so all consent
 * categories start as denied. The 500 ms wait_for_update window gives the
 * consent popup time to fire gtag('consent','update',{...granted}) before
 * any cached GA pings are flushed.
 *
 * The phpcs:ignore below suppresses OutputNotEscaped for the wp_json_encode()
 * return value: the consent-defaults array contains only plugin-controlled
 * string constants and a single integer literal — no user input reaches this output.
 *
 * @since 2.0.0
 * @return void
 */
add_action(
	'wp_head',
	function () {
		$settings            = get_settings_controller();
		$is_tracking_enabled = $settings->get_bool( OPT_IS_GA_TRACKING_ENABLED );

		if ( $is_tracking_enabled ) {
			// Preconnect hint: allows the browser to resolve the DNS and open a TCP/TLS
			// connection to googletagmanager.com in advance, reducing latency when
			// gtag.js is dynamically loaded after the user consents.
			//
			// The preconnect itself transmits no user data — it only establishes a
			// connection. The actual gtag.js request (and any data transmission) still
			// only happens after explicit user consent via loadGoogleAnalytics().
			echo "<link rel=\"preconnect\" href=\"https://www.googletagmanager.com\">\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static string with no user input.

			// Always: initialise dataLayer and define the gtag() stub so any
			// gtag() calls before gtag.js loads are safely queued locally.
			$head_js = 'window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}';

			// Consent Mode v2: set all consent categories to denied before any
			// interaction. The 500 ms wait_for_update window allows the consent
			// popup to fire gtag('consent','update') before GA flushes cached pings.
			$is_consent_mode = $settings->get_bool( OPT_GA_CONSENT_MODE_ENABLED );

			if ( $is_consent_mode ) {
				$defaults = wp_json_encode(
					array(
						'analytics_storage'  => 'denied',
						'ad_storage'         => 'denied',
						'ad_user_data'       => 'denied',
						'ad_personalization' => 'denied',
						'wait_for_update'    => 500,
					)
				);

				$head_js .= sprintf( 'gtag("consent","default",%s);', $defaults ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output; values are plugin-controlled constants only.
			}

			printf( "<script>%s</script>\n", $head_js ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $head_js contains only plugin-controlled JS; no user input.
		}
	},
	1
);

/**
 * Register Google Analytics as a blockable script handle.
 *
 * @since 2.0.0
 */
add_filter(
	'mwg_blockable_script_handles',
	function ( $handles ) {
		$handles[] = GA_SCRIPT_HANDLE;

		return $handles;
	}
);

/**
 * Provide Google Analytics tracker metadata.
 *
 * The pattern matches the gtag('config', ...) call in the stub inline script
 * so the Script_Blocker can detect the tracker and list it in the info overlay.
 *
 * can-defer is false because the gtag.js script is loaded directly by
 * loadGoogleAnalytics() in JS after the user consents — not via the standard
 * insertBlockedScripts() re-injection mechanism. Allowing re-injection would
 * cause the config stub to run a second time (harmless but unnecessary).
 *
 * @since 2.0.0
 */
add_filter(
	'mwg_tracker_' . GA_SCRIPT_HANDLE,
	function () {
		return [
			'pattern'     => '/gtag\(["\']config["\']/',
			'field'       => 'outerhtml',
			'description' => __( 'Google Analytics', 'mini-wp-gdpr' ),
			'can-defer'   => false,
		];
	}
);

/**
 * Register the Google Analytics config stub as an inline WordPress script.
 *
 * The stub queues gtag('js', new Date()) and gtag('config', 'ID') in dataLayer.
 * The gtag.js SDK is NOT injected here — it is loaded by the consent popup JS
 * (loadGoogleAnalytics()) only after the user explicitly accepts tracking.
 *
 * When gtag.js loads after consent it processes the full dataLayer queue in order:
 *   1. gtag('consent','default',{...denied})  — from wp_head stub (Consent Mode)
 *   2. gtag('js', new Date())                 — page-load timestamp
 *   3. gtag('config', 'ID')                   — tracker configuration
 *   4. gtag('consent','update',{...granted})  — from consentToScripts() in JS
 *
 * This approach is GDPR-compliant: the stub itself transmits no data to Google
 * servers. Data transmission begins only when gtag.js loads after consent.
 *
 * @since 2.0.0
 * @return void
 */
add_action(
	'mwg_inject_tracker_' . GA_SCRIPT_HANDLE,
	function () {
		$settings            = get_settings_controller();
		$is_tracking_enabled = $settings->get_bool( OPT_IS_GA_TRACKING_ENABLED );
		$tracker_code        = '';
		$should_output_stub  = false;

		if ( $is_tracking_enabled ) {
			$raw_code = $settings->get_string( OPT_GA_TRACKING_CODE );

			if ( empty( $raw_code ) ) {
				error_log( __FUNCTION__ . ' Missing Google Analytics tracker code' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			} elseif ( ! preg_match( '/^(G|UA|YT|MO)-[a-zA-Z0-9-]+$/', $raw_code ) ) {
				error_log( __FUNCTION__ . ' Invalid Google Analytics tracker code' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			} else {
				$tracker_code       = sanitize_text_field( $raw_code );
				$should_output_stub = ! empty( $tracker_code );
			}
		}

		if ( $should_output_stub ) {
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion,WordPress.WP.EnqueuedResourceParameters.NotInFooter,WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion -- Empty-src inline stub; caching version is irrelevant (no URL to cache). In head so config calls are queued before any theme/plugin gtag() calls.
			wp_register_script( GA_SCRIPT_HANDLE, '', [], false, false );
			wp_enqueue_script( GA_SCRIPT_HANDLE );

			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- GA config stub must be in head; gtag('js') and gtag('config') must be queued before any theme/plugin gtag() calls.
			wp_add_inline_script(
				GA_SCRIPT_HANDLE,
				sprintf(
					// GA config stub: queues gtag('js') and gtag('config') in dataLayer.
					// Does NOT load gtag.js — that happens after consent via JS.
					// @see assets/mini-gdpr-cookie-popup.js MiniGdprPopup.loadGoogleAnalytics()
					//
					// When gtag.js loads after consent it replays the full dataLayer queue,
					// initialising GA with the correct page-load timestamp and config.
					'gtag("js",new Date());gtag("config","%s");',
					esc_js( $tracker_code )
				)
			);
		}
	}
);

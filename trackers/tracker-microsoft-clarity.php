<?php

/**
 * Microsoft Clarity tracker integration.
 *
 * Registers the Microsoft Clarity handle as blockable, provides tracker metadata,
 * and outputs a minimal clarity stub in the page head when Clarity is enabled.
 *
 * Microsoft Clarity delay-loading workflow:
 *   1. wp_enqueue_scripts: clarity stub registered as an inline script.
 *      The stub sets up window.clarity as a queue-backed function.
 *      The clarity.ms/tag/<ID> script is NOT loaded at this point — no data
 *      is sent to Microsoft servers before the user consents.
 *   2. User accepts consent: JS calls loadMicrosoftClarity() using mgwcsData.clarityId.
 *   3. clarity.ms/tag/<ID> loads dynamically; it discovers window.clarity.q and
 *      replays all queued calls, completing Clarity initialisation.
 *
 * This ensures clarity.ms never loads before explicit user consent (GDPR-compliant).
 * The clarity stub itself is harmless: it creates a local queue but sends no data.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Output a preconnect hint for clarity.ms in <head> at the earliest priority.
 *
 * Allows the browser to pre-establish a DNS/TCP/TLS connection to clarity.ms before
 * the user consents, reducing latency when loadMicrosoftClarity() injects the SDK.
 *
 * The preconnect itself transmits no user data — it only resolves DNS and opens a
 * TCP/TLS socket. The actual clarity.ms request (and all data transmission) still
 * only happens after explicit user consent via loadMicrosoftClarity().
 *
 * @since 2.0.0
 * @return void
 */
add_action(
	'wp_head',
	function () {
		$settings   = get_settings_controller();
		$is_enabled = $settings->get_bool( OPT_IS_MS_CLARITY_TRACKING_ENABLED );

		if ( $is_enabled ) {
			echo "<link rel=\"preconnect\" href=\"https://www.clarity.ms\">\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static string with no user input.
		}
	},
	1
);

/**
 * Register Microsoft Clarity as a blockable script handle.
 *
 * @since 2.0.0
 */
add_filter(
	'mwg_blockable_script_handles',
	function ( $handles ) {
		$handles[] = MS_CLARITY_SCRIPT_HANDLE;

		return $handles;
	}
);

/**
 * Provide Microsoft Clarity tracker metadata.
 *
 * The pattern matches the window.clarity queue stub in the inline script
 * so the Script_Blocker can detect the tracker and list it in the info overlay.
 *
 * can-defer is false because the clarity.ms/tag/<ID> script is loaded directly
 * by loadMicrosoftClarity() in JS after the user consents — not via the standard
 * insertBlockedScripts() re-injection mechanism. Allowing re-injection would
 * cause the stub to run a second time (harmless but unnecessary).
 *
 * @since 2.0.0
 */
add_filter(
	'mwg_tracker_' . MS_CLARITY_SCRIPT_HANDLE,
	function () {
		return [
			'pattern'     => '/window\.clarity\s*=/',
			'field'       => 'outerhtml',
			'description' => __( 'Microsoft Clarity', 'mini-wp-gdpr' ),
			'can-defer'   => false,
		];
	}
);

/**
 * Register the Microsoft Clarity queue stub as an inline WordPress script.
 *
 * The stub creates window.clarity as a queue-backed function. The clarity.ms
 * script is NOT injected here — it is loaded by the consent popup JS
 * (loadMicrosoftClarity()) only after the user explicitly accepts tracking.
 *
 * This approach is GDPR-compliant: the stub itself transmits no data to
 * Microsoft servers. Data transmission begins only when clarity.ms/tag/<ID>
 * loads after consent.
 *
 * @since 2.0.0
 * @return void
 */
add_action(
	'mwg_inject_tracker_' . MS_CLARITY_SCRIPT_HANDLE,
	function () {
		$settings           = get_settings_controller();
		$is_enabled         = $settings->get_bool( OPT_IS_MS_CLARITY_TRACKING_ENABLED );
		$tracker_id         = '';
		$should_output_stub = false;

		if ( $is_enabled ) {
			$raw_id = $settings->get_string( OPT_MS_CLARITY_ID );

			if ( empty( $raw_id ) ) {
				error_log( __FUNCTION__ . ' Missing Microsoft Clarity project ID' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			} elseif ( ! preg_match( '/^[a-zA-Z0-9_-]{1,32}$/', $raw_id ) ) {
				error_log( __FUNCTION__ . ' Invalid Microsoft Clarity project ID' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			} else {
				$tracker_id         = sanitize_text_field( $raw_id );
				$should_output_stub = ! empty( $tracker_id );
			}
		}

		if ( $should_output_stub ) {
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion,WordPress.WP.EnqueuedResourceParameters.NotInFooter,WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion -- Empty-src inline stub; caching version is irrelevant (no URL to cache). In head so clarity queue exists before any theme/plugin calls.
			wp_register_script( MS_CLARITY_SCRIPT_HANDLE, '', [], false, false );
			wp_enqueue_script( MS_CLARITY_SCRIPT_HANDLE );

			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Clarity stub must be in head; the queue needs to exist before any theme/plugin calls clarity().
			wp_add_inline_script(
				MS_CLARITY_SCRIPT_HANDLE,
				// clarity stub: creates window.clarity as a queue-backed function.
				// Does NOT load clarity.ms — that happens after consent via JS.
				// @see assets/mini-gdpr-cookie-popup.js MiniGdprPopup.loadMicrosoftClarity()
				'window.clarity=window.clarity||function(){(window.clarity.q=window.clarity.q||[]).push(arguments)};'
			);
		}
	}
);

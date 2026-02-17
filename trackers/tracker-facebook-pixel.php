<?php

/**
 * Facebook Pixel tracker integration.
 *
 * Registers the Facebook Pixel handle as blockable, provides tracker metadata,
 * and outputs a minimal fbq stub in the page head when the pixel is enabled.
 *
 * Facebook Pixel delay-loading workflow:
 *   1. wp_enqueue_scripts: fbq stub registered as an inline script.
 *      The stub sets up window.fbq and queues fbq('init') + fbq('track','PageView').
 *      fbevents.js is NOT loaded at this point — no tracking data is sent.
 *   2. User accepts consent: JS calls loadFacebookPixel() using mgwcsData.fbpxId.
 *   3. fbevents.js loads dynamically and replays the queued init + PageView events.
 *
 * This ensures fbevents.js never loads before explicit user consent (GDPR-compliant).
 * The fbq stub itself is harmless: it creates a queue but sends no data without fbevents.js.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Register Facebook Pixel as a blockable script handle.
 *
 * @since 2.0.0
 */
add_filter(
	'mwg_blockable_script_handles',
	function ( $handles ) {
		$handles[] = FB_PIXEL_SCRIPT_HANDLE;

		return $handles;
	}
);

/**
 * Provide Facebook Pixel tracker metadata.
 *
 * The pattern matches the fbq("init", ...) call in the stub inline script
 * so the Script_Blocker can detect the tracker and list it in the info overlay.
 *
 * can-defer is false because the re-injection of the stub via insertBlockedScripts()
 * is not needed — fbevents.js is loaded directly by loadFacebookPixel() in JS
 * after the user consents. Allowing the standard re-injection mechanism would
 * cause the stub to run a second time (harmless but unnecessary).
 *
 * @since 2.0.0
 */
add_filter(
	'mwg_tracker_' . FB_PIXEL_SCRIPT_HANDLE,
	function () {
		return [
			'pattern'     => '/fbq\(["\']init["\']/',
			'field'       => 'outerhtml',
			'description' => __( 'Facebook Pixel', 'mini-wp-gdpr' ),
			'can-defer'   => false,
		];
	}
);

/**
 * Register the Facebook Pixel fbq stub as an inline WordPress script.
 *
 * The stub creates window.fbq as a queue-backed function and registers the
 * fbq('init') and fbq('track','PageView') calls in the queue. fbevents.js is
 * NOT injected here — it is loaded by the consent popup JS (loadFacebookPixel())
 * only after the user explicitly accepts tracking.
 *
 * This approach is GDPR-compliant: the stub itself transmits no data to Meta
 * servers. Data transmission begins only when fbevents.js loads after consent.
 *
 * @since 2.0.0
 * @return void
 */
add_action(
	'mwg_inject_tracker_' . FB_PIXEL_SCRIPT_HANDLE,
	function () {
		$settings           = get_settings_controller();
		$is_enabled         = $settings->get_bool( OPT_IS_FB_PIXEL_TRACKING_ENABLED );
		$tracker_id         = '';
		$should_output_stub = false;

		if ( $is_enabled ) {
			$raw_id = $settings->get_string( OPT_FB_PIXEL_ID );

			if ( ! empty( $raw_id ) ) {
				$tracker_id         = sanitize_text_field( $raw_id );
				$should_output_stub = ! empty( $tracker_id );
			}
		}

		if ( $should_output_stub ) {
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion,WordPress.WP.EnqueuedResourceParameters.NotInFooter,WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion -- Empty-src inline stub; caching version is irrelevant (no URL to cache). In head so fbq queue exists before theme/plugin fbq() calls.
			wp_register_script( FB_PIXEL_SCRIPT_HANDLE, '', [], false, false );
			wp_enqueue_script( FB_PIXEL_SCRIPT_HANDLE );

			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Pixel stub must be in head; fbq queue needs to exist before any theme/plugin calls fbq().
			wp_add_inline_script(
				FB_PIXEL_SCRIPT_HANDLE,
				sprintf(
					// fbq stub: creates window.fbq as a queue-backed function.
					// Does NOT load fbevents.js — that happens after consent via JS.
					// @see assets/mini-gdpr-cookie-popup.js MiniGdprPopup.loadFacebookPixel()
					//
					// FB Pixel Consent API: fbq('consent','revoke') is called immediately after
					// the stub is created and BEFORE fbq('init'). This is a defensive GDPR guard:
					// if fbevents.js loads unexpectedly (e.g. via a theme or another plugin),
					// the pixel initialises in revoked state and sends no data until our JS
					// calls fbq('consent','grant') in loadFacebookPixel() after the user accepts.
					// @see https://developers.facebook.com/docs/meta-pixel/implementation/gdpr
					'window.fbq=function(){window.fbq.callMethod?' .
					'window.fbq.callMethod.apply(window.fbq,arguments)' .
					':window.fbq.queue.push(arguments)};' .
					'if(!window._fbq)window._fbq=window.fbq;' .
					'window.fbq.push=window.fbq;window.fbq.loaded=!0;' .
					'window.fbq.version="2.0";window.fbq.queue=[];' .
					'fbq("consent","revoke");fbq("init","%s");fbq("track","PageView");',
					esc_js( $tracker_id )
				)
			);
		}
	}
);

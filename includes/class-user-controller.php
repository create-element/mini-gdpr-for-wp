<?php

/**
 * User GDPR consent controller.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Manages reading and writing of per-user GDPR consent records.
 *
 * Consent timestamps are stored as human-readable strings in user meta
 * (META_ACCEPTED_GDPR_WHEN_FIRST and META_ACCEPTED_GDPR_WHEN_RECENT).
 *
 * @since 1.0.0
 */
class User_Controller extends Component {

	/**
	 * Constructor.
	 *
	 * @param string $name    Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( string $name, string $version ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Kept for future extension and docblock clarity.
		parent::__construct( $name, $version );
	}

	/**
	 * Record GDPR acceptance for a user, preserving the original acceptance date.
	 *
	 * Sets META_ACCEPTED_GDPR_WHEN_FIRST only if not already set, then always
	 * updates META_ACCEPTED_GDPR_WHEN_RECENT. Defaults to the current user when
	 * $user_id is not provided or is invalid.
	 *
	 * @since 1.0.0
	 * @param int $user_id WordPress user ID (0 = current user).
	 * @return void
	 */
	public function accept_gdpr_terms_now( int $user_id = 0 ) {
		if ( $user_id <= 0 ) {
			$user_id = get_current_user_id();
		}

		$now_h = get_date_time_now_h();

		if ( ! empty( $now_h ) ) {
			if ( empty( get_user_meta( $user_id, META_ACCEPTED_GDPR_WHEN_FIRST, true ) ) ) {
				update_user_meta( $user_id, META_ACCEPTED_GDPR_WHEN_FIRST, $now_h );
			}

			update_user_meta( $user_id, META_ACCEPTED_GDPR_WHEN_RECENT, $now_h );
		}
	}

	/**
	 * Record GDPR rejection for a user (cookie consent popup).
	 *
	 * Stores a rejection timestamp in user meta so the server-side state matches
	 * the client-side localStorage/cookie set by rejectConsent() in JS.
	 * Only the most recent rejection is stored — no first-rejection audit trail.
	 *
	 * @since 2.0.0
	 * @param int $user_id WordPress user ID (0 = current user).
	 * @return void
	 */
	public function reject_gdpr_terms_now( int $user_id = 0 ) {
		if ( $user_id <= 0 ) {
			$user_id = get_current_user_id();
		}

		$now_h = get_date_time_now_h();

		if ( ! empty( $now_h ) ) {
			update_user_meta( $user_id, META_REJECTED_GDPR_WHEN, $now_h );
		}
	}

	/**
	 * Check whether a user has rejected GDPR cookie consent.
	 *
	 * @since 2.0.0
	 * @param int $user_id WordPress user ID.
	 * @return bool True when a valid rejection timestamp exists.
	 */
	public function has_user_rejected_gdpr( int $user_id ) {
		return ! empty( $this->when_did_user_reject_gdpr( $user_id ) );
	}

	/**
	 * Return the formatted timestamp of a user's most recent GDPR rejection.
	 *
	 * Returns null when no rejection record exists, the stored value is not a
	 * valid date, or the year predates EARLIEST_GDPR_YEAR.
	 *
	 * @since 2.0.0
	 * @param int    $user_id WordPress user ID.
	 * @param string $format  PHP date() format string (default 'Y-m-d H:i:s T').
	 * @return string|null Formatted date string, or null on failure.
	 */
	public function when_did_user_reject_gdpr( int $user_id, string $format = '' ) {
		$when = null;

		if ( empty( $format ) ) {
			$format = 'Y-m-d H:i:s T';
		}

		if ( $user_id > 0 ) {
			$when_raw = get_user_meta( $user_id, META_REJECTED_GDPR_WHEN, true );

			if ( ! empty( $when_raw ) ) {
				try {
					$when_datetime = new \DateTime( $when_raw );

					if ( intval( $when_datetime->format( 'Y' ) ) >= EARLIEST_GDPR_YEAR ) {
						$when = $when_datetime->format( $format );
					}
				} catch ( \Exception $e ) {
					error_log( __FUNCTION__ . ' : Bad date: ' . $when_raw ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					$when = null;
				}
			}
		}

		return $when;
	}

	/**
	 * Delete all GDPR consent records for a user.
	 *
	 * Removes acceptance (first + most-recent) and rejection meta keys.
	 * Defaults to the current user when $user_id is not provided or is invalid.
	 *
	 * @since 1.0.0
	 * @param int $user_id WordPress user ID (0 = current user).
	 * @return void
	 */
	public function clear_gdpr_accepted_status( int $user_id = 0 ) {
		if ( $user_id <= 0 ) {
			$user_id = get_current_user_id();
		}

		delete_user_meta( $user_id, META_ACCEPTED_GDPR_WHEN_FIRST );
		delete_user_meta( $user_id, META_ACCEPTED_GDPR_WHEN_RECENT );
		delete_user_meta( $user_id, META_REJECTED_GDPR_WHEN );
	}

	/**
	 * Check whether a user has accepted GDPR.
	 *
	 * @since 1.0.0
	 * @param int $user_id WordPress user ID.
	 * @return bool True when a valid acceptance timestamp exists.
	 */
	public function has_user_accepted_gdpr( int $user_id ) {
		return ! empty( $this->when_did_user_accept_gdpr( $user_id ) );
	}

	/**
	 * Return the formatted timestamp of a user's most recent GDPR acceptance.
	 *
	 * Returns null when no acceptance record exists, the stored value is not a
	 * valid date, or the year predates EARLIEST_GDPR_YEAR.
	 *
	 * @since 1.0.0
	 * @param int    $user_id WordPress user ID.
	 * @param string $format  PHP date() format string (default 'Y-m-d H:i:s T').
	 * @return string|null Formatted date string, or null on failure.
	 */
	public function when_did_user_accept_gdpr( int $user_id, string $format = '' ) {
		$when = null;

		if ( empty( $format ) ) {
			$format = 'Y-m-d H:i:s T';
		}

		if ( $user_id > 0 ) {
			$when_raw = get_user_meta( $user_id, META_ACCEPTED_GDPR_WHEN_RECENT, true );

			if ( ! empty( $when_raw ) ) {
				try {
					$when_datetime = new \DateTime( $when_raw );

					if ( intval( $when_datetime->format( 'Y' ) ) >= EARLIEST_GDPR_YEAR ) {
						$when = $when_datetime->format( $format );
					}
				} catch ( \Exception $e ) {
					error_log( __FUNCTION__ . ' : Bad date: ' . $when_raw ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					$when = null;
				}
			}
		}

		return $when;
	}
}

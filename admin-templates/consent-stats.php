<?php
/**
 * Admin template: Consent statistics dashboard.
 *
 * Displays a summary of registered user consent/rejection counts using
 * direct $wpdb COUNT queries on wp_usermeta.
 *
 * Included by Settings::render_settings_page() outside the settings form
 * (read-only — no form inputs, no save action).
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template file included within a method; variables are scoped to the calling method, not truly global.

defined( 'ABSPATH' ) || die();

global $wpdb;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
// Reason: live admin stats that must reflect the current state of wp_usermeta;
// caching these would show stale counts immediately after a consent change.

$total_users = (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->users}" );

$accepted_count = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = %s",
		META_ACCEPTED_GDPR_WHEN_RECENT
	)
);

$rejected_count = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = %s",
		META_REJECTED_GDPR_WHEN
	)
);

$decided_count = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key IN (%s, %s)",
		META_ACCEPTED_GDPR_WHEN_RECENT,
		META_REJECTED_GDPR_WHEN
	)
);

// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

$undecided_count = max( 0, $total_users - $decided_count );

/**
 * Format a percentage for display.
 *
 * Returns '—' when the base is zero to avoid division-by-zero.
 *
 * @param int $count Numerator.
 * @param int $base  Denominator.
 * @return string Formatted percentage string, e.g. '42%'.
 */
$pct = static function ( int $count, int $base ): string {
	if ( $base <= 0 ) {
		return '—';
	}

	return round( ( $count / $base ) * 100 ) . '%';
};

$stats = array(
	array(
		'label'    => __( 'Registered Users', 'mini-wp-gdpr' ),
		'count'    => $total_users,
		'pct'      => '',
		'modifier' => '',
	),
	array(
		'label'    => __( 'Accepted', 'mini-wp-gdpr' ),
		'count'    => $accepted_count,
		'pct'      => $pct( $accepted_count, $total_users ),
		'modifier' => 'mwg-stat--accepted',
	),
	array(
		'label'    => __( 'Rejected', 'mini-wp-gdpr' ),
		'count'    => $rejected_count,
		'pct'      => $pct( $rejected_count, $total_users ),
		'modifier' => 'mwg-stat--rejected',
	),
	array(
		'label'    => __( 'Undecided', 'mini-wp-gdpr' ),
		'count'    => $undecided_count,
		'pct'      => $pct( $undecided_count, $total_users ),
		'modifier' => 'mwg-stat--undecided',
	),
);

printf( '<h2>%s</h2>', esc_html__( 'Consent Statistics', 'mini-wp-gdpr' ) );

echo '<div class="pp-columns pp-inline-flex mwg-stat-cards">';

foreach ( $stats as $stat ) {
	printf(
		'<div class="pp-panel pp-column mwg-stat-card %s">
			<p class="mwg-stat-label">%s</p>
			<p class="mwg-stat-number">%s</p>
			%s
		</div>',
		esc_attr( $stat['modifier'] ),
		esc_html( $stat['label'] ),
		esc_html( number_format_i18n( $stat['count'] ) ),
		! empty( $stat['pct'] ) ? sprintf( '<p class="mwg-stat-pct">%s</p>', esc_html( $stat['pct'] ) ) : ''
	);
}

echo '</div>';

echo '<p class="pp-help">';
esc_html_e( 'Accepted / Rejected counts reflect registered users who made a decision via the cookie consent popup. Undecided users have not yet interacted with the popup, or their consent has not been recorded (e.g. guest visitors).', 'mini-wp-gdpr' );
echo '</p>';

<?php

/**
 * Base component class.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Base class for all Mini WP GDPR plugin classes.
 *
 * Provides the $name and $version properties shared across all plugin
 * components. This replaces the pp-core.php Component class removed in M3.
 *
 * @since 2.0.0
 */
class Component {

	/**
	 * Plugin slug / text domain.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Plugin version string.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructor.
	 *
	 * @param string $name    Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( string $name, string $version ) {
		$this->name    = $name;
		$this->version = $version;
	}
}

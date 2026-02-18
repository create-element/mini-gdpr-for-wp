<?php

/**
 * PHPStan bootstrap file — defines constants that require runtime WordPress functions
 * (plugin_dir_path, plugin_dir_url, trailingslashit) so static analysis can resolve them.
 *
 * This file is NOT loaded by WordPress. It is only used by PHPStan.
 */

define( 'PP_MWG_DIR', __DIR__ . '/' );
define( 'PP_MWG_URL', 'https://example.com/wp-content/plugins/mini-gdpr-for-wp/' );
define( 'PP_MWG_ADMIN_TEMPLATES_DIR', __DIR__ . '/admin-templates/' );
define( 'PP_MWG_PUBLIC_TEMPLATES_DIR', __DIR__ . '/public-templates/' );
define( 'PP_MWG_ASSETS_DIR', __DIR__ . '/assets/' );
define( 'PP_MWG_ASSETS_URL', 'https://example.com/wp-content/plugins/mini-gdpr-for-wp/assets/' );

<?php
/**
 * Plugin Name:       Latest Comments Extended
 * Description:       Extends the Latest Comments block with additional features.
 * Plugin URI:        https://github.com/s3rgiosan/latest-comments-extended
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            Sérgio Santos
 * Author URI:        https://s3rgiosan.dev/?utm_source=wp-plugins&utm_medium=latest-comments-extended&utm_campaign=author-uri
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       latest-comments-extended
 *
 * @package           LatestCommentsExtended
 */

namespace S3S\WP\LatestCommentsExtended;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'S3S_LATEST_COMMENTS_EXTENDED_PATH', plugin_dir_path( __FILE__ ) );
define( 'S3S_LATEST_COMMENTS_EXTENDED_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( S3S_LATEST_COMMENTS_EXTENDED_PATH . 'vendor/autoload.php' ) ) {
	require_once S3S_LATEST_COMMENTS_EXTENDED_PATH . 'vendor/autoload.php';
}

( Plugin::get_instance() )->setup();
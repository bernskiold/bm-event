<?php
/**
 * Plugin Name: BM Event
 * Plugin URI:  https://bernskioldmedia.com
 * Description: Functionality to help you market and manage an event. From showing speakers to a live agenda for one or multiple days.
 * Version:     1.2.4
 * Requires at least: 7.0
 * Tested up to: 7.1
 * Requires PHP: 8.2
 * Author:      Bernskiold Media
 * Author URI:  https://bernskioldmedia.com
 * Text Domain: bm-event
 * Domain Path: /languages/
 *
 * @package BernskioldMedia\WP\Event
 */

use BernskioldMedia\WP\Event\Install;
use BernskioldMedia\WP\Event\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Autoloader
 */
require_once __DIR__ . '/autoloader.php';

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}
/**
 * Basic Constants
 */
define( 'BM_EVENT_FILE_PATH', __FILE__ );

/**
 * Grant the post type and taxonomy capabilities to the
 * default roles and flush the rewrite rules on activation.
 */
register_activation_hook( __FILE__, [ Install::class, 'install' ] );

/**
 * Initialize and boot the plugin.
 *
 * @return Plugin
 */
function bm_event(): Plugin {
	return Plugin::instance();
}

bm_event();

<?php
/**
 * Hookable Interface
 *
 * @package BernskioldMedia\WP\Event
 */

namespace BernskioldMedia\WP\Event;

defined( 'ABSPATH' ) || exit;

/**
 * Classes that register their WordPress hooks when the plugin boots.
 */
interface Hookable {

	/**
	 * Register the actions and filters for the class.
	 */
	public static function hooks(): void;

}

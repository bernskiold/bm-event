<?php
/**
 * Logger
 *
 * A small logger exposed as static functions in the plugin. Messages
 * are written with error_log(), so they end up wherever PHP and
 * WordPress (WP_DEBUG_LOG) send errors on the site.
 *
 * By default only errors and above are logged. Define ENABLE_LOGGING
 * as true to log everything, or LOGGING_LEVEL as one of the level
 * names below (e.g. 'warning') to choose the threshold.
 *
 * @package BernskioldMedia\WP\Event
 */

namespace BernskioldMedia\WP\Event;

defined( 'ABSPATH' ) || exit;

/**
 * Class Log
 *
 * @package BernskioldMedia\WP\Event
 */
class Log {

	/**
	 * Log levels and their severity, following RFC 5424.
	 */
	public const LEVELS = [
		'debug'     => 100,
		'info'      => 200,
		'notice'    => 250,
		'warning'   => 300,
		'error'     => 400,
		'critical'  => 500,
		'alert'     => 550,
		'emergency' => 600,
	];

	/**
	 * Get the minimum level that is written to the log.
	 */
	public static function get_threshold(): int {
		if ( defined( 'LOGGING_LEVEL' ) ) {
			$level = LOGGING_LEVEL;

			if ( is_numeric( $level ) ) {
				return (int) $level;
			}

			return self::LEVELS[ strtolower( (string) $level ) ] ?? self::LEVELS['error'];
		}

		if ( defined( 'ENABLE_LOGGING' ) && true === ENABLE_LOGGING ) {
			return self::LEVELS['debug'];
		}

		return self::LEVELS['error'];
	}

	/**
	 * Write a message to the log if it meets the threshold.
	 *
	 * @param  string $level    Level name.
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function log( string $level, $message, array $context = [] ): void {
		if ( ( self::LEVELS[ $level ] ?? 0 ) < self::get_threshold() ) {
			return;
		}

		$line = sprintf( '[%s] %s: %s', Plugin::TEXTDOMAIN, strtoupper( $level ), $message );

		if ( $context ) {
			$line .= ' ' . wp_json_encode( $context );
		}

		error_log( $line ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	/**
	 * Debug
	 *
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function debug( $message, array $context = [] ): void {
		self::log( 'debug', $message, $context );
	}

	/**
	 * Info
	 *
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function info( $message, array $context = [] ): void {
		self::log( 'info', $message, $context );
	}

	/**
	 * Notice
	 *
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function notice( $message, array $context = [] ): void {
		self::log( 'notice', $message, $context );
	}

	/**
	 * Warning
	 *
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function warning( $message, array $context = [] ): void {
		self::log( 'warning', $message, $context );
	}

	/**
	 * Error
	 *
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function error( $message, array $context = [] ): void {
		self::log( 'error', $message, $context );
	}

	/**
	 * Critical
	 *
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function critical( $message, array $context = [] ): void {
		self::log( 'critical', $message, $context );
	}

	/**
	 * Alert
	 *
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function alert( $message, array $context = [] ): void {
		self::log( 'alert', $message, $context );
	}

	/**
	 * Emergency
	 *
	 * @param  string $message  Message.
	 * @param  array  $context  Data.
	 */
	public static function emergency( $message, array $context = [] ): void {
		self::log( 'emergency', $message, $context );
	}

}

<?php

namespace BernskioldMedia\WP\Event;

use BernskioldMedia\WP\Event\Data\Session;

/**
 * Class Shortcodes
 *
 * @package BernskioldMedia\WP\Event
 */
class SessionHelper
{
	public static function get_start_time( int $session_id ): ?string {
		$session = new Session($session_id);
		return $session->get_start_date_from_datetime('Y-m-d H:i:s');
	}

	public static function get_start_timestamp( int $session_id ): ?int {
		$session = new Session($session_id);
		return $session->get_start_date_from_datetime('U');
	}

	public static function get_end_time( int $session_id ): ?string {
		$session = new Session($session_id);
		return $session->get_end_date_from_datetime('Y-m-d H:i:s');
	}

	public static function get_end_timestamp( int $session_id ): ?int {
		$session = new Session($session_id);
		return $session->get_end_date_from_datetime('U');
	}

	public static function get_embed_url( int $session_id ): ?string {
		$session = new Session($session_id);
		$meta = $session->get_embed_url();

		return is_string( $meta ) ? $meta : null;
	}

	public static function the_player( int $session_id ): void {
		if ( ! self::get_embed_url( $session_id ) ) {
			return;
		}

		echo wp_oembed_get( self::get_embed_url( $session_id ), [
			'width'  => 1280,
			'height' => 720,
		] );
	}

	public static function should_load_next(): bool {
		$session = Queries::get_next_session();

		if ( ! $session ) {
			return false;
		}

		$seconds_before = self::get_seconds_before_to_load_next();
		$time_left      = self::get_start_timestamp( $session->ID ) - current_time( 'timestamp' );

		return $time_left <= $seconds_before;
	}

	public static function get_seconds_before_to_load_next(): int {
		return apply_filters( 'bmevents_seconds_before_to_load_next', MINUTE_IN_SECONDS * 5 );
	}
}

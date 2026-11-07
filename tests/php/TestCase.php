<?php

namespace BernskioldMedia\WP\Event\Tests;

use WP_UnitTestCase;

/**
 * Base test case with helpers for creating sessions, speakers and tracks.
 */
abstract class TestCase extends WP_UnitTestCase {

	/**
	 * Create a published session.
	 *
	 * @param  array  $fields  ACF field values keyed by field name.
	 * @param  array  $args    Post arguments.
	 */
	protected function create_session( array $fields = [], array $args = [] ): int {
		$id = self::factory()->post->create( array_merge( [
			'post_type'   => 'session',
			'post_status' => 'publish',
			'post_title'  => 'Session',
		], $args ) );

		foreach ( $fields as $name => $value ) {
			update_field( $name, $value, $id );
		}

		return $id;
	}

	/**
	 * Create a published speaker.
	 *
	 * @param  array  $fields  ACF field values keyed by field name.
	 */
	protected function create_speaker( array $fields = [] ): int {
		$id = self::factory()->post->create( [
			'post_type'   => 'speaker',
			'post_status' => 'publish',
			'post_title'  => trim( ( $fields['speaker_first_name'] ?? '' ) . ' ' . ( $fields['speaker_last_name'] ?? '' ) ) ?: 'Speaker',
		] );

		foreach ( $fields as $name => $value ) {
			update_field( $name, $value, $id );
		}

		return $id;
	}

	/**
	 * Create a track term.
	 */
	protected function create_track( string $name = 'Main Stage' ): int {
		return self::factory()->term->create( [
			'taxonomy' => 'track',
			'name'     => $name,
		] );
	}

	/**
	 * A datetime string relative to now, in the site's time zone.
	 */
	protected function datetime( string $modifier ): string {
		return wp_date( 'Y-m-d H:i:s', strtotime( $modifier ) );
	}

}

<?php
/**
 * Session Data
 *
 * @author  Bernskiold Media <info@bernskioldmedia.com>
 * @package BernskioldMedia\WP\Event
 * @since   1.0.0
 */

namespace BernskioldMedia\WP\Event\Data;

use BernskioldMedia\WP\Event\Abstracts\Data;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Class Session
 *
 * @package BernskioldMedia\WP\Event
 */
class Session extends Data {

	/**
	 * Reference to the data store.
	 *
	 * @var string
	 */
	protected static $data_store = \BernskioldMedia\WP\Event\Data_Stores\Session::class;

	/**
	 * Get Session Title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return get_the_title( $this->get_id() );
	}

	/**
	 * Get session topic.
	 *
	 * @return string|null
	 */
	public function get_topic(): ?string {
		return $this->get_prop( 'session_topic' );
	}

	/**
	 * Get the session short description
	 *
	 * @return string|null
	 */
	public function get_short_description(): ?string {
		return $this->get_prop( 'session_short_description' );
	}

	/**
	 * Get the type of the session with both value and label.
	 *
	 * @return array|null
	 */
	public function get_session_type(): ?array {
		return $this->get_prop( 'session_type' );
	}

	/**
	 * Get the session type key (value).
	 *
	 * @return string|null
	 */
	public function get_session_type_key(): ?string {
		$session_type = $this->get_session_type();

		return $session_type ? $session_type['value'] : null;
	}

	/**
	 * Get the session type label. Localized.
	 *
	 * @return string|null
	 */
	public function get_session_type_label(): ?string {
		$session_type = $this->get_session_type();

		return $session_type ? $session_type['label'] : null;
	}

	/**
	 * Get the date the session is at.
	 *
	 * @param  string  $format  date() compatible string.
	 *
	 * @return string|null
	 */
	public function get_date( string $format = 'Y-m-d' ): ?string {
		$date = $this->get_prop( 'session_date' );

		if ( ! $date ) {
			return null;
		}

		return date( $format, strtotime( $date ) );
	}

	/**
	 * Get the start date the session is at.
	 *
	 * @param  string  $format  date() compatible string.
	 *
	 * @return string|null
	 */
	public function get_start_date_from_datetime( string $format = 'Y-m-d' ): ?string {
		$date = $this->get_prop( 'session_start_datetime' );

		if ( ! $date ) {
			return null;
		}

		return date( $format, strtotime( $date ) );
	}

	/**
	 * Get the end date the session is at.
	 *
	 * @param  string  $format  date() compatible string.
	 *
	 * @return string|null
	 */
	public function get_end_date_from_datetime( string $format = 'Y-m-d' ): ?string {
		$date = $this->get_prop( 'session_end_datetime' );

		if ( ! $date ) {
			return null;
		}

		return date( $format, strtotime( $date ) );
	}

	/**
	 * Get the time when the session starts.
	 *
	 * @param  string  $format  date() compatible format.
	 *
	 * @return string|null
	 */
	public function get_start_time( string $format = 'H:i' ): ?string {
		$time = $this->get_prop( 'session_start_time' );

		if ( ! $time ) {
			return null;
		}

		return date( $format, strtotime( $time ) );
	}

	/**
	 * Get the time when the session starts.
	 *
	 * @param  string  $format  date() compatible format.
	 *
	 * @return string|null
	 */
	public function get_start_time_from_datetime( string $format = 'H:i' ): ?string {
		$time = $this->get_prop( 'session_start_datetime' );

		if ( ! $time ) {
			return null;
		}

		return date( $format, strtotime( $time ) );
	}

	public function get_start_datetime( ): ?string {
		$time = $this->get_prop( 'session_start_datetime' );

		if ( ! $time ) {
			return null;
		}

		return date( $time );
	}

	/**
	 * Get the time when the session ends.
	 *
	 * @param  string  $format  date() compatible format.
	 *
	 * @return string|null
	 */
	public function get_end_time( string $format = 'H:i' ): ?string {
		$time = $this->get_prop( 'session_end_time' );

		if ( ! $time ) {
			return null;
		}

		return date( $format, strtotime( $time ) );
	}

	/**
	 * Get the time when the session ends.
	 *
	 * @param  string  $format  date() compatible format.
	 *
	 * @return string|null
	 */
	public function get_end_time_from_datetime( string $format = 'H:i' ): ?string {
		$time = $this->get_prop( 'session_end_datetime' );

		if ( ! $time ) {
			return null;
		}

		return date( $format, strtotime( $time ) );
	}

	public function get_end_datetime( ): ?string {
		$time = $this->get_prop( 'session_end_datetime' );

		if ( ! $time ) {
			return null;
		}

		return date( $time );
	}

	/**
	 * Get an array of speaker IDs associated with this session.
	 *
	 * @return array|null
	 */
	public function get_speaker_ids(): ?array {

		$speakers = $this->get_prop( 'session_speakers_ids' );

		if ( ! $speakers ) {
			return null;
		}

		return $this->get_prop( 'session_speakers_ids' );
	}

	/**
	 * Get the speakers (with objects) associated with this session.
	 *
	 * @param  string  $type
	 *
	 * @return Speaker[]|null
	 */
	public function get_speakers( $type = ARRAY_A ): ?array {
		$ids = $this->get_speaker_ids();

		if ( ! $ids ) {
			return null;
		}

		$speakers = [];

		foreach ( $ids as $id ) {

			if ( ARRAY_A === $type ) {
				$speakers[] = ( new Speaker( $id ) )->to_array();
			} else {
				$speakers[] = new Speaker( $id );
			}
		}

		return $speakers;
	}

	/**
	 * Get the booking URL.
	 *
	 * @return string|null
	 */
	public function get_booking_url(): ?string {
		return $this->get_prop( 'session_booking_url' );
	}

	public function get_embed_url(): ?string {
		return $this->get_prop( 'session_live_url' );
	}

	public function get_youtube_id(): ?string {
		preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', (string) $this->get_embed_url(), $match );

		return $match[1] ?? null;
	}

	public static function get_all( array $args = [] ): array {
		$query_args = [
			'post_type'      => 'session',
			'orderby'        => 'meta_value',
			'meta_key'       => 'session_start_time',
			'meta_type'      => 'TIME',
			'order'          => 'ASC',
			'posts_per_page' => 100,
		];


		if( !empty( $args )){
			foreach( $args as $key => $value ){
				$query_args[$key] = $value;
			}
		}

		$sessions = new WP_Query( $query_args );

		return $sessions->get_posts();
	}

	public function get_prev_next(): array {
		$sessions        = wp_list_pluck( self::get_all(), 'ID' );
		$key_for_session = array_search( $this->get_id(), $sessions, true );
		$keys            = self::array_neighbor( $sessions, $key_for_session, true );

		return [
			'next' => $sessions[ $keys['prev'] ],
			'prev' => $sessions[ $keys['next'] ],
		];
	}

	/**
	 * Function to get array keys on either side of a given key. If the
	 * initial key is first in the array then prev is null. If the initial
	 * key is last in the array, then next is null.
	 *
	 * If wrap is true and the initial key is last, then next is the first
	 * element in the array.
	 *
	 * If wrap is true and the initial key is first, then prev is the last
	 * element in the array.
	 *
	 * @param  array  $arr
	 * @param  string  $key
	 * @param  bool  $wrap
	 *
	 * @return array $return
	 */
	public static function array_neighbor( array $arr, string $key, bool $wrap = false ): array {

		krsort( $arr );
		$keys       = array_keys( $arr );
		$keyIndexes = array_flip( $keys );

		$return = [];
		if ( isset( $keys[ $keyIndexes[ $key ] - 1 ] ) ) {
			$return['prev'] = $keys[ $keyIndexes[ $key ] - 1 ];
		} else {
			$return['prev'] = null;
		}

		if ( isset( $keys[ $keyIndexes[ $key ] + 1 ] ) ) {
			$return['next'] = $keys[ $keyIndexes[ $key ] + 1 ];
		} else {
			$return['next'] = null;
		}

		if ( false !== $wrap && empty( $return['prev'] ) ) {
			$end            = end( $arr );
			$return['prev'] = key( $arr );
		}

		if ( false !== $wrap && empty( $return['next'] ) ) {
			$beginning      = reset( $arr );
			$return['next'] = key( $arr );
		}

		return $return;
	}

	public static function get_current_or_next_session(){

	}

}

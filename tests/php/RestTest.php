<?php

namespace BernskioldMedia\WP\Event\Tests;

use WP_REST_Request;
use WP_REST_Server;

/**
 * @covers \BernskioldMedia\WP\Event\Rest\NextVideo
 * @covers \BernskioldMedia\WP\Event\Rest\SessionGrid
 * @covers \BernskioldMedia\WP\Event\Abstracts\REST_Endpoint
 * @covers \BernskioldMedia\WP\Event\Abstracts\Data_Store_WP
 * @covers \BernskioldMedia\WP\Event\Queries
 */
class RestTest extends TestCase {

	private WP_REST_Server $server;

	public function set_up(): void {
		parent::set_up();

		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		$this->server   = $wp_rest_server;
		do_action( 'rest_api_init', $this->server );
	}

	public function tear_down(): void {
		global $wp_rest_server;
		$wp_rest_server = null;
		unset( $_COOKIE['event_timezone'] );

		parent::tear_down();
	}

	private function get( string $route ) {
		return $this->server->dispatch( new WP_REST_Request( 'GET', $route ) );
	}

	public function test_routes_are_registered(): void {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/bm-events/v1/next-video', $routes );
		$this->assertArrayHasKey( '/bm-events/v1/session-grid', $routes );
	}

	public function test_next_video_without_sessions(): void {
		$response = $this->get( '/bm-events/v1/next-video' );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( [
			'id'             => null,
			'start_time'     => null,
			'end_time'       => null,
			'should_refresh' => false,
		], $response->get_data() );
	}

	public function test_next_video_returns_the_next_session(): void {
		$this->create_session( [ 'session_start_datetime' => $this->datetime( '-1 day' ) ] );
		$soon  = $this->create_session( [
			'session_start_datetime' => $this->datetime( '+2 hours' ),
			'session_end_datetime'   => $this->datetime( '+3 hours' ),
		] );
		$this->create_session( [ 'session_start_datetime' => $this->datetime( '+1 day' ) ] );

		$data = $this->get( '/bm-events/v1/next-video' )->get_data();

		$this->assertSame( $soon, $data['id'] );
		$this->assertSame( get_field( 'session_start_datetime', $soon ), $data['start_time'] );
		$this->assertFalse( $data['should_refresh'] );
	}

	public function test_next_video_asks_to_refresh_shortly_before_the_start(): void {
		$this->create_session( [ 'session_start_datetime' => $this->datetime( '+2 minutes' ) ] );

		$this->assertTrue( $this->get( '/bm-events/v1/next-video' )->get_data()['should_refresh'] );
	}

	public function test_next_video_is_public(): void {
		wp_set_current_user( 0 );

		$this->assertSame( 200, $this->get( '/bm-events/v1/next-video' )->get_status() );
		$this->assertSame( 200, $this->get( '/bm-events/v1/session-grid' )->get_status() );
	}

	public function test_session_grid_renders_the_sessions(): void {
		$this->create_session( [
			'session_start_datetime' => '2030-05-01 09:00:00',
			'session_end_datetime'   => '2030-05-01 10:00:00',
			'session_topic'          => 'Opening',
		], [ 'post_title' => 'Welcome' ] );

		$html = $this->get( '/bm-events/v1/session-grid' )->get_data()['html'];

		$this->assertStringContainsString( 'Welcome', $html );
		$this->assertStringContainsString( 'Opening', $html );
		$this->assertStringContainsString( '1 May 09:00', $html );
		$this->assertStringContainsString( '10:00 BST', $html );
	}

	/**
	 * @dataProvider provide_timezone_cookies
	 */
	public function test_session_grid_uses_the_timezone_cookie( string $cookie, string $expected ): void {
		$this->create_session( [
			'session_start_datetime' => '2030-01-15 09:00:00',
			'session_end_datetime'   => '2030-01-15 10:00:00',
		] );

		$_COOKIE['event_timezone'] = $cookie;

		$html = $this->get( '/bm-events/v1/session-grid' )->get_data()['html'];

		$this->assertStringContainsString( $expected, $html );
	}

	public static function provide_timezone_cookies(): array {
		return [
			'city'           => [ 'Europe/Stockholm', '15 Jan 10:00' ],
			'whole offset'   => [ 'UTC+2', '15 Jan 11:00' ],
			'partial offset' => [ 'UTC+5.5', '15 Jan 14:30' ],
			'unknown'        => [ 'Not/AZone', '15 Jan 09:00' ],
		];
	}

	public function test_session_rest_fields(): void {
		$speaker = $this->create_speaker( [ 'speaker_first_name' => 'Ada', 'speaker_last_name' => 'Lovelace' ] );
		$session = $this->create_session( [
			'session_topic'        => 'Computing',
			'session_speakers_ids' => [ $speaker ],
		] );

		$data = $this->get( '/wp/v2/session/' . $session )->get_data();

		$this->assertSame( 'Computing', $data['topic'] );
		$this->assertSame( 'Ada Lovelace', $data['speakers'][0]['name'] );
		$this->assertArrayHasKey( 'start_datetime', $data );
	}

	public function test_sessions_can_be_filtered_by_track(): void {
		$track = $this->create_track();
		$in    = $this->create_session( [ 'session_start_datetime' => '2030-05-01 09:00:00' ] );
		$this->create_session( [ 'session_start_datetime' => '2030-05-01 10:00:00' ] );
		wp_set_object_terms( $in, [ $track ], 'track' );

		$request = new WP_REST_Request( 'GET', '/wp/v2/session' );
		$request->set_param( 'track', $track );

		$this->assertSame( [ $in ], wp_list_pluck( $this->server->dispatch( $request )->get_data(), 'id' ) );
	}

}

<?php

namespace BernskioldMedia\WP\Event\Tests;

/**
 * @covers \BernskioldMedia\WP\Event\Shortcodes
 * @covers \BernskioldMedia\WP\Event\Queries
 */
class ShortcodesTest extends TestCase {

	public function tear_down(): void {
		unset( $_COOKIE['event_timezone'] );

		parent::tear_down();
	}

	public function test_display_events_renders_the_selector_and_grid(): void {
		$this->create_session( [
			'session_start_datetime' => '2030-05-01 09:00:00',
			'session_end_datetime'   => '2030-05-01 10:00:00',
		], [ 'post_title' => 'Workshop' ] );

		$html = do_shortcode( '[bm_display_events]' );

		$this->assertStringContainsString( 'class="js--timezone-selector"', $html );
		$this->assertStringContainsString( 'js--session-grid', $html );
		$this->assertStringContainsString( 'selected="selected" value="Europe/London"', $html );
		$this->assertStringContainsString( 'Workshop', $html );
	}

	public function test_display_events_selects_the_cookie_timezone(): void {
		$_COOKIE['event_timezone'] = 'Europe/Stockholm';

		$this->assertStringContainsString( 'selected="selected" value="Europe/Stockholm"', do_shortcode( '[bm_display_events]' ) );
	}

	public function test_live_viewer_placeholder_without_sessions(): void {
		$html = do_shortcode( '[bm_liveviewer]' );

		$this->assertStringContainsString( 'data-event-id="0"', $html );
		$this->assertStringContainsString( 'session-player-placeholder', $html );
	}

	public function test_live_viewer_shows_the_current_session(): void {
		$current = $this->create_session( [
			'session_start_datetime' => $this->datetime( '-10 minutes' ),
			'session_end_datetime'   => $this->datetime( '+50 minutes' ),
		] );
		$this->create_session( [
			'session_start_datetime' => $this->datetime( '+2 hours' ),
			'session_end_datetime'   => $this->datetime( '+3 hours' ),
		] );

		$this->assertStringContainsString( 'data-event-id="' . $current . '"', do_shortcode( '[bm_liveviewer]' ) );
	}

	public function test_live_viewer_switches_to_the_next_session_shortly_before_it_starts(): void {
		$this->create_session( [
			'session_start_datetime' => $this->datetime( '-50 minutes' ),
			'session_end_datetime'   => $this->datetime( '+10 minutes' ),
		] );
		$next = $this->create_session( [
			'session_start_datetime' => $this->datetime( '+2 minutes' ),
			'session_end_datetime'   => $this->datetime( '+1 hour' ),
		] );

		$this->assertStringContainsString( 'data-event-id="' . $next . '"', do_shortcode( '[bm_liveviewer]' ) );
	}

}

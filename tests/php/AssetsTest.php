<?php

namespace BernskioldMedia\WP\Event\Tests;

use BernskioldMedia\WP\Event\Assets;

/**
 * @covers \BernskioldMedia\WP\Event\Assets
 */
class AssetsTest extends TestCase {

	public function tear_down(): void {
		wp_dequeue_style( 'bm-event-public' );
		wp_deregister_script_module( 'bm-event-live-viewer' );
		wp_deregister_script_module( 'bm-event-time-switcher' );

		parent::tear_down();
	}

	private function visit( string $content ): void {
		$this->go_to( get_permalink( self::factory()->post->create( [ 'post_content' => $content ] ) ) );
		do_action( 'wp_enqueue_scripts' );
	}

	private function printed_modules(): string {
		ob_start();
		wp_script_modules()->print_enqueued_script_modules();
		wp_script_modules()->print_script_module_data();

		return ob_get_clean();
	}

	public function test_reads_the_generated_asset_files(): void {
		$meta = Assets::get_asset_meta( 'dist/blocks/track-timetable' );

		$this->assertContains( 'wp-blocks', $meta['dependencies'] );
		$this->assertNotEmpty( $meta['version'] );

		$missing = Assets::get_asset_meta( 'dist/does-not-exist' );

		$this->assertSame( [], $missing['dependencies'] );
	}

	public function test_style_is_enqueued_in_the_head_for_posts_with_the_block(): void {
		$this->visit( '<!-- wp:bm/track-timetable /-->' );

		$this->assertTrue( wp_style_is( 'bm-event-public', 'enqueued' ) );
		$this->assertStringEndsWith( '/assets/styles/dist/app.css', wp_styles()->registered['bm-event-public']->src );
	}

	public function test_style_is_not_enqueued_elsewhere(): void {
		$this->visit( '<p>Hello</p>' );

		$this->assertFalse( wp_style_is( 'bm-event-public', 'enqueued' ) );
	}

	public function test_public_css_can_be_disabled(): void {
		add_filter( 'bm_event_disable_public_css', '__return_true' );

		$this->visit( '<!-- wp:bm/track-timetable /-->' );

		$this->assertFalse( wp_style_is( 'bm-event-public', 'enqueued' ) );
	}

	public function test_no_jquery_or_select2_on_the_front_end(): void {
		$this->visit( '<!-- wp:bm/track-timetable /-->[bm_display_events][bm_liveviewer]' );

		$this->assertFalse( wp_script_is( 'select2', 'registered' ) );
		$this->assertFalse( wp_style_is( 'select2', 'registered' ) );
		$this->assertFalse( wp_script_is( 'jquery', 'enqueued' ) );
	}

	public function test_live_viewer_module_on_pages_with_the_shortcode(): void {
		$this->visit( '[bm_liveviewer]' );

		$output = $this->printed_modules();

		$this->assertStringContainsString( '/assets/scripts/dist/live-viewer.js', $output );
		$this->assertStringContainsString( 'wp-script-module-data-bm-event-live-viewer', $output );
		$this->assertStringContainsString( '"pollingInterval":30000', $output );
		$this->assertStringNotContainsString( 'time-switcher.js', $output );
	}

	public function test_time_switcher_module_on_pages_with_the_shortcode(): void {
		$this->visit( '[bm_display_events]' );

		$output = $this->printed_modules();

		$this->assertStringContainsString( '/assets/scripts/dist/time-switcher.js', $output );
		$this->assertStringContainsString( 'wp-script-module-data-bm-event-time-switcher', $output );
		$this->assertMatchesRegularExpression( '#"apiUrl":"[^"]*bm-events\\\\?/v1"#', $output );
	}

	public function test_no_modules_without_a_post(): void {
		$this->go_to( home_url( '/?p=999999' ) );
		do_action( 'wp_enqueue_scripts' );

		$this->assertStringNotContainsString( 'live-viewer.js', $this->printed_modules() );
	}

}

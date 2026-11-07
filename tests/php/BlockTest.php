<?php

namespace BernskioldMedia\WP\Event\Tests;

use BernskioldMedia\WP\Event\Blocks\Track_Timetable\Track_Timetable_Block;
use WP_Block_Type_Registry;

/**
 * @covers \BernskioldMedia\WP\Event\Blocks\Track_Timetable\Track_Timetable_Block
 * @covers \BernskioldMedia\WP\Event\Plugin::register_blocks
 */
class BlockTest extends TestCase {

	private function block_type(): \WP_Block_Type {
		return WP_Block_Type_Registry::get_instance()->get_registered( 'bm/track-timetable' );
	}

	/**
	 * Render the block as it would be in post content.
	 */
	private function render( array $attributes = [] ): string {
		$markup = '<!-- wp:bm/track-timetable ' . ( $attributes ? wp_json_encode( $attributes ) . ' ' : '' ) . '/-->';

		return do_blocks( $markup );
	}

	public function test_block_is_registered_from_block_json(): void {
		$type = $this->block_type();

		$this->assertSame( 3, $type->api_version );
		$this->assertSame( 'event', $type->category );
		$this->assertSame( 0, $type->attributes['track_id']['default'] );
		$this->assertFalse( $type->attributes['showTimezoneSelector']['default'] );
		$this->assertSame( [ 'list', 'grid' ], wp_list_pluck( $type->styles, 'name' ) );
		$this->assertTrue( $type->is_dynamic() );
	}

	public function test_block_assets(): void {
		$type = $this->block_type();

		$this->assertSame( [ 'bm-track-timetable-editor-script' ], $type->editor_script_handles );
		$this->assertSame( [ 'bm-event-public' ], $type->style_handles );
		$this->assertSame( [ 'bm-event-block' ], $type->editor_style_handles );
		$this->assertSame( [ Track_Timetable_Block::get_view_module_id() ], $type->view_script_module_ids );
		$this->assertSame( 'bm-track-timetable-view-script-module', Track_Timetable_Block::get_view_module_id() );

		$script = wp_scripts()->registered['bm-track-timetable-editor-script'];
		$this->assertStringEndsWith( '/dist/blocks/track-timetable.js', $script->src );
		$this->assertContains( 'wp-blocks', $script->deps );
		$this->assertContains( 'wp-core-data', $script->deps );
		$this->assertNotContains( 'jquery', $script->deps );
	}

	public function test_renders_nothing_without_sessions(): void {
		$this->assertSame( '', trim( $this->render() ) );
	}

	public function test_renders_the_sessions_as_a_list(): void {
		$speaker = $this->create_speaker( [
			'speaker_first_name' => 'Ada',
			'speaker_last_name'  => 'Lovelace',
			'speaker_title'      => 'Programmer',
		] );

		$this->create_session( [
			'session_start_datetime'    => '2030-05-01 09:00:00',
			'session_start_time'        => '09:00',
			'session_end_time'          => '10:00',
			'session_date'              => '20300501',
			'session_topic'             => 'Keynote',
			'session_short_description' => 'A warm welcome.',
			'session_speakers_ids'      => [ $speaker ],
		], [ 'post_title' => 'Opening' ] );

		$html = $this->render();

		$this->assertStringContainsString( 'class="sessions is-style-list wp-block-bm-track-timetable"', $html );
		$this->assertStringContainsString( 'session-list-item', $html );
		$this->assertStringContainsString( 'Opening', $html );
		$this->assertStringContainsString( 'Keynote', $html );
		$this->assertStringContainsString( 'A warm welcome.', $html );
		$this->assertStringContainsString( 'Ada Lovelace', $html );
		$this->assertStringContainsString( 'Programmer', $html );
		$this->assertStringContainsString( 'datetime="2030-05-01 09:00"', $html );
		$this->assertStringContainsString( 'js-event-start-time', $html );
		$this->assertStringNotContainsString( 'js--track-timetable-timezone-selector', $html );
	}

	public function test_renders_the_grid_style(): void {
		$this->create_session( [ 'session_start_datetime' => '2030-05-01 09:00:00' ] );

		$html = $this->render( [ 'className' => 'is-style-grid' ] );

		$this->assertStringContainsString( 'is-style-grid', $html );
		$this->assertStringContainsString( 'session-grid-item', $html );
		$this->assertStringNotContainsString( 'session-list-item', $html );
	}

	public function test_get_style(): void {
		$this->assertSame( 'list', Track_Timetable_Block::get_style( [] ) );
		$this->assertSame( 'list', Track_Timetable_Block::get_style( [ 'className' => 'is-style-list foo' ] ) );
		$this->assertSame( 'grid', Track_Timetable_Block::get_style( [ 'className' => 'foo is-style-grid' ] ) );
	}

	public function test_filters_by_track(): void {
		$track = $this->create_track();
		$in    = $this->create_session( [ 'session_start_datetime' => '2030-05-01 09:00:00' ], [ 'post_title' => 'On the track' ] );
		$this->create_session( [ 'session_start_datetime' => '2030-05-01 10:00:00' ], [ 'post_title' => 'Elsewhere' ] );
		wp_set_object_terms( $in, [ $track ], 'track' );

		$html = $this->render( [ 'track_id' => $track ] );

		$this->assertStringContainsString( 'On the track', $html );
		$this->assertStringNotContainsString( 'Elsewhere', $html );
	}

	public function test_timezone_selector_anchor_and_custom_class(): void {
		$this->create_session( [ 'session_start_datetime' => '2030-05-01 09:00:00' ] );

		$html = $this->render( [
			'showTimezoneSelector' => true,
			'anchor'               => 'agenda',
			'className'            => 'my-agenda',
		] );

		$this->assertStringContainsString( 'js--track-timetable-timezone-selector', $html );
		$this->assertStringContainsString( 'id="agenda"', $html );
		$this->assertStringContainsString( 'my-agenda', $html );
	}

	public function test_restores_the_global_post(): void {
		$this->create_session( [ 'session_start_datetime' => '2030-05-01 09:00:00' ] );
		$page = self::factory()->post->create();

		$this->go_to( get_permalink( $page ) );
		the_post();

		$this->render();

		$this->assertSame( $page, get_the_ID() );
	}

	public function test_rendering_enqueues_the_view_module_and_style(): void {
		$this->create_session( [ 'session_start_datetime' => '2030-05-01 09:00:00' ] );

		$this->render( [ 'showTimezoneSelector' => true ] );

		$this->assertTrue( wp_style_is( 'bm-event-public', 'enqueued' ) );

		ob_start();
		wp_script_modules()->print_enqueued_script_modules();
		$modules = ob_get_clean();

		$this->assertStringContainsString( '/dist/blocks/track-timetable/view.js', $modules );
	}

	public function test_view_module_data(): void {
		update_option( 'timezone_string', 'Europe/Stockholm' );
		update_option( 'gmt_offset', '' );

		$data = apply_filters( 'script_module_data_' . Track_Timetable_Block::get_view_module_id(), [] );

		$this->assertSame( 'Europe/Stockholm', $data['siteTimezone'] );
		$this->assertArrayHasKey( 'gmtOffset', $data );
		$this->assertSame( 'Select Your Timezone...', $data['texts']['selectTz'] );
		$this->assertSame( 'See the schedule in your local time.', $data['texts']['tzLabel'] );
	}

}

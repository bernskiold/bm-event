<?php

namespace BernskioldMedia\WP\Event\Tests;

use BernskioldMedia\WP\Event\Assets;
use BernskioldMedia\WP\Event\Plugin;
use BernskioldMedia\WP\Event\Shortcodes;

/**
 * @covers \BernskioldMedia\WP\Event\Plugin
 */
class PluginTest extends TestCase {

	public function test_instance_is_a_singleton(): void {
		$this->assertInstanceOf( Plugin::class, bm_event() );
		$this->assertSame( bm_event(), Plugin::instance() );
	}

	public function test_version_matches_plugin_header_and_package_json(): void {
		$header  = get_file_data( BM_EVENT_FILE_PATH, [ 'Version' => 'Version' ] );
		$package = json_decode( file_get_contents( Plugin::get_path( 'package.json' ) ), true );

		$this->assertSame( $header['Version'], Plugin::get_version() );
		$this->assertSame( $package['version'], Plugin::get_version() );
	}

	public function test_plugin_header_declares_the_floors(): void {
		$header = get_file_data( BM_EVENT_FILE_PATH, [
			'RequiresWP'  => 'Requires at least',
			'RequiresPHP' => 'Requires PHP',
		] );

		$this->assertSame( '7.0', $header['RequiresWP'] );
		$this->assertSame( '8.2', $header['RequiresPHP'] );
	}

	public function test_paths_and_urls_resolve_inside_the_plugin(): void {
		$this->assertSame( dirname( BM_EVENT_FILE_PATH ) . '/blocks/blocks-manifest.php', Plugin::get_path( 'blocks/blocks-manifest.php' ) );
		$this->assertStringEndsWith( '/assets/styles/dist/app.css', Plugin::get_assets_url( 'styles/dist/app.css' ) );
		$this->assertFileExists( Plugin::get_path( 'blocks/blocks-manifest.php' ) );
		$this->assertFileExists( Plugin::get_path( 'dist/blocks/track-timetable.asset.php' ) );
	}

	public function test_textdomain(): void {
		$this->assertSame( 'bm-event', Plugin::get_textdomain() );
	}

	public function test_hooks_are_registered(): void {
		$this->assertNotFalse( has_action( 'init', [ Plugin::class, 'load_languages' ] ) );
		$this->assertNotFalse( has_action( 'init', [ Plugin::class, 'register_blocks' ] ) );
		$this->assertNotFalse( has_filter( 'block_categories_all', [ Plugin::class, 'setup_block_categories' ] ) );
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', [ Assets::class, 'public_styles' ] ) );
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', [ Assets::class, 'public_scripts' ] ) );
		$this->assertTrue( shortcode_exists( 'bm_display_events' ) );
		$this->assertTrue( shortcode_exists( 'bm_liveviewer' ) );
	}

	public function test_adds_the_events_block_category(): void {
		$categories = Plugin::setup_block_categories( [ [ 'slug' => 'text', 'title' => 'Text' ] ] );

		$this->assertSame( [ 'text', 'event' ], wp_list_pluck( $categories, 'slug' ) );
	}

	public function test_load_template_prefers_the_theme_override(): void {
		switch_theme( 'twentytwentyfive' );
		unset( $GLOBALS['wp_stylesheet_path'], $GLOBALS['wp_template_path'] );

		$override = get_stylesheet_directory() . '/components/event/liveviewer.php';

		wp_mkdir_p( dirname( $override ) );
		file_put_contents( $override, '<p>Theme live viewer</p>' );

		try {
			ob_start();
			Plugin::load_template( 'event/liveviewer' );
			$output = ob_get_clean();
		} finally {
			unlink( $override );
			rmdir( dirname( $override ) );
			rmdir( dirname( $override, 2 ) );
			unset( $GLOBALS['wp_stylesheet_path'], $GLOBALS['wp_template_path'] );
		}

		$this->assertSame( '<p>Theme live viewer</p>', $output );
	}

	public function test_load_template_passes_variables(): void {
		$template = Plugin::get_path( 'views/event/test-variables.php' );
		file_put_contents( $template, '<?php echo esc_html( $greeting );' );

		try {
			ob_start();
			Plugin::load_template( 'event/test-variables', [ 'greeting' => 'Hello' ] );
			$output = ob_get_clean();
		} finally {
			unlink( $template );
		}

		$this->assertSame( 'Hello', $output );
	}

}

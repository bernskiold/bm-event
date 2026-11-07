<?php
/**
 * Handles the loading of scripts and styles for the
 * theme through the proper enqueuing methods.
 *
 * @author  Bernskiold Media <info@bernskioldmedia.com>
 * @package BernskioldMedia\WP\Event
 * @since   1.0.0
 **/

namespace BernskioldMedia\WP\Event;

defined( 'ABSPATH' ) || exit;

/**
 * Assets Class
 *
 * @package BernskioldMedia\WP\Event
 */
class Assets implements Hookable {

	/**
	 * Assets Constructor
	 */
	public static function hooks(): void {

		// Styles.
		add_action( 'wp_enqueue_scripts', [ self::class, 'public_styles' ] );

		// Scripts.
		add_action( 'wp_enqueue_scripts', [ self::class, 'public_scripts' ] );
		add_filter( 'script_module_data_bm-event-live-viewer', [ self::class, 'live_viewer_data' ] );
		add_filter( 'script_module_data_bm-event-time-switcher', [ self::class, 'time_switcher_data' ] );

	}

	/**
	 * Get the dependencies and version that wp-scripts generated for
	 * a built file, given its path relative to the plugin without
	 * the extension.
	 *
	 * @param  string $file  E.g. 'assets/scripts/dist/app'.
	 *
	 * @return array{dependencies: string[], version: string}
	 */
	public static function get_asset_meta( string $file ): array {
		$path = Plugin::get_path( $file . '.asset.php' );

		$meta = file_exists( $path ) ? require $path : [];

		return [
			'dependencies' => $meta['dependencies'] ?? [],
			'version'      => $meta['version'] ?? Plugin::get_version(),
		];
	}

	/**
	 * Whether the plugin's stylesheet is disabled.
	 *
	 * We may optionally set a filter to disable public CSS.
	 * For example, if we want to bring in the styles in a theme.
	 */
	protected static function is_css_disabled(): bool {
		return true === apply_filters( 'bm_event_disable_public_css', false );
	}

	/**
	 * Register the stylesheet handles that the block's block.json
	 * uses as its style and editorStyle.
	 */
	public static function register_block_styles(): void {
		if ( self::is_css_disabled() ) {
			return;
		}

		$version = self::get_asset_meta( 'assets/styles/dist/app' )['version'];

		wp_register_style( 'bm-event-public', Plugin::get_assets_url( 'styles/dist/app.css' ), [], $version, 'all' );
		wp_register_style( 'bm-event-block', Plugin::get_assets_url( 'styles/dist/app.css' ), [], $version, 'all' );
	}

	/**
	 * Enqueue the stylesheet in the head on pages where the content
	 * has the block. Elsewhere it is enqueued when the block renders.
	 **/
	public static function public_styles(): void {
		if ( self::is_css_disabled() ) {
			return;
		}

		if ( self::has_blocks() ) {
			wp_enqueue_style( 'bm-event-public' );
		}
	}

	/**
	 * Register the shortcode script modules and enqueue them on
	 * pages that use the shortcodes.
	 **/
	public static function public_scripts(): void {
		self::register_script_module( 'bm-event-live-viewer', 'assets/scripts/dist/live-viewer' );
		self::register_script_module( 'bm-event-time-switcher', 'assets/scripts/dist/time-switcher' );

		if ( self::has_shortcode( 'bm_liveviewer' ) ) {
			wp_enqueue_script_module( 'bm-event-live-viewer' );
		}

		if ( self::has_shortcode( 'bm_display_events' ) ) {
			wp_enqueue_script_module( 'bm-event-time-switcher' );
		}
	}

	/**
	 * Data for the live viewer script module.
	 *
	 * @param  array $data
	 *
	 * @return array
	 */
	public static function live_viewer_data( array $data ): array {
		return array_merge( $data, [
			'apiUrl'          => rest_url( 'bm-events/v1' ),
			'pollingInterval' => 30000,
		] );
	}

	/**
	 * Data for the time switcher script module.
	 *
	 * @param  array $data
	 *
	 * @return array
	 */
	public static function time_switcher_data( array $data ): array {
		return array_merge( $data, [
			'apiUrl' => rest_url( 'bm-events/v1' ),
		] );
	}

	/**
	 * Register a built script module with its generated dependencies.
	 *
	 * @param  string $id
	 * @param  string $file  Path relative to the plugin without the extension.
	 */
	protected static function register_script_module( string $id, string $file ): void {
		$meta = self::get_asset_meta( $file );

		wp_register_script_module( $id, Plugin::get_url( $file . '.js' ), $meta['dependencies'], $meta['version'] );
	}

	/**
	 * Whether the current post has the given shortcode.
	 *
	 * @param  string $tag
	 */
	protected static function has_shortcode( string $tag ): bool {
		$post = get_post();

		return $post instanceof \WP_Post && has_shortcode( $post->post_content, $tag );
	}

	/**
	 * Check if we have our plugin blocks.
	 *
	 * @return bool
	 */
	protected static function has_blocks(): bool {
		return has_block( 'bm/track-timetable' );
	}
}

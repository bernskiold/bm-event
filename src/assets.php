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
		add_action( 'enqueue_block_editor_assets', [ self::class, 'block_editor_assets' ] );

		// Scripts.
		add_action( 'wp_enqueue_scripts', [ self::class, 'public_scripts' ] );

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
	 * Register a built script with its generated dependencies.
	 *
	 * @param  string   $handle
	 * @param  string   $file          Path relative to the plugin without the extension.
	 * @param  string[] $dependencies  Additional dependencies.
	 */
	protected static function register_script( string $handle, string $file, array $dependencies = [] ): void {
		$meta = self::get_asset_meta( $file );

		wp_register_script( $handle, Plugin::get_url( $file . '.js' ), array_unique( array_merge( $meta['dependencies'], $dependencies ) ), $meta['version'], true );
	}

	/**
	 * Registers and enqueues public stylesheets.
	 **/
	public static function public_styles() {
		global $post;

		// We may optionally set a filter to disable public CSS.
		// For example, if we want to bring in the styles in a theme.
		if ( true === apply_filters( 'bm_event_disable_public_css', false ) ) {
			return;
		}

		/**
		 * Register Main Stylesheet.
		 */
		wp_register_style( 'bm-event-public', Plugin::get_assets_url( 'styles/dist/app.css' ), [], self::get_asset_meta( 'assets/styles/dist/app' )['version'], 'all' );
		wp_register_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.0/dist/css/select2.min.css', false, '4.0.0', 'all' );

		/**
		 * Enqueue Stylesheets.
		 */
		if ( self::has_blocks() ) {
			wp_enqueue_style( 'bm-event-public' );
			wp_enqueue_style( 'select2' );
		}

		if ( has_shortcode( $post->post_content, 'bm_display_events' ) ) {
			wp_enqueue_style( 'select2' );
		}
	}

	/**
	 * Load the styles for the block editor.
	 */
	public static function block_editor_assets() {

		// We may optionally set a filter to disable public CSS.
		// For example, if we want to bring in the styles in a theme.
		if ( true === apply_filters( 'bm_event_disable_public_css', false ) ) {
			return;
		}

		wp_enqueue_style( 'bm-event-block', Plugin::get_assets_url( 'styles/dist/app.css' ), [], self::get_asset_meta( 'assets/styles/dist/app' )['version'], 'all' );

	}

	/**
	 * Enqueue Scripts on public side
	 **/
	public static function public_scripts() {
		global $post;

		/**
		 * Register the compiled script files.
		 */
		wp_register_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.0/dist/js/select2.min.js', [ 'jquery' ], '4.0.0', true );
		self::register_script( 'bm-event-app', 'assets/scripts/dist/app', [ 'jquery', 'select2' ] );
		self::register_script( 'liveviewer', 'assets/scripts/dist/live-viewer' );
		self::register_script( 'time-switcher', 'assets/scripts/dist/time-switcher' );

		/**
		 * Only load if we have the right blocks.
		 */
		if ( self::has_blocks() ) {

			wp_enqueue_script( 'bm-event-app' );
			wp_enqueue_script( 'select2' );

			wp_localize_script( 'bm-event-app', 'BMEvent', [
				'texts'        => [
					'selectTz' => __( 'Select Your Timezone...', 'bm-events' ),
					'tzLabel'  => __( 'See the schedule in your local time.', 'bm-events' ),
				],
				'gmtOfset'     => get_option( 'gmt_offset' ),
				'siteTimezone' => get_option( 'timezone_string' ),
			] );

		}

		if ( has_shortcode( $post->post_content, 'bm_liveviewer' ) ) {
			wp_enqueue_script( 'liveviewer' );

			wp_localize_script( 'liveviewer', 'BMELiveViewer', [
				'apiUrl'          => rest_url( 'bm-events/v1' ),
				'pollingInterval' => 30000,
			] );
		}

		if ( has_shortcode( $post->post_content, 'bm_display_events' ) ) {
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'time-switcher' );

			wp_localize_script( 'time-switcher', 'BMETimeSwitcher', [
				'apiUrl' => rest_url( 'bm-events/v1' ),
			] );
		}
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

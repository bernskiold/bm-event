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
		// add_action( 'admin_enqueue_scripts', [ self::class, 'admin_styles' ] );

		// Scripts.
		add_action( 'wp_enqueue_scripts', [ self::class, 'public_scripts' ] );
		// add_action( 'admin_enqueue_scripts', [ self::class, 'admin_scripts' ] );

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
		wp_register_style( 'bm-event-public', Plugin::get_assets_url() . '/styles/dist/app.css', false, Plugin::get_version(), 'all' );
		wp_register_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.0/dist/css/select2.min.css', false, '4.0.0', 'all' );

		/**
		 * Enqueue Stylesheets.
		 */
		if ( self::has_blocks() ) {
			wp_enqueue_style( 'bm-event-public' );
			wp_enqueue_style( 'select2' );
		}

		if(has_shortcode($post->post_content, 'bm_display_events')) {
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

		wp_enqueue_style( 'bm-event-block', Plugin::get_assets_url( 'styles/dist/app.css' ), false, Plugin::get_version(), 'all' );

	}

	/**
	 * Registers and enqueues plugin admin stylesheets.
	 **/
	public static function admin_styles() {

		/**
		 * Register Main Stylesheet.
		 */
		wp_register_style( 'bm-event-admin', Plugin::get_assets_url() . '/styles/dist/admin.css', false, Plugin::get_version(), 'all' );

		/**
		 * Enqueue Stylesheets.
		 */
		wp_enqueue_style( 'bm-event-admin' );

	}

	/**
	 * Enqueue Scripts on public side
	 *
	 * We want to allow the use of good script debugging here too,
	 * so be mindful and use the SCRIPTS_DEBUG constant
	 * to load both minified for production and non-minified files
	 * for testing purposes.
	 **/
	public static function public_scripts() {
		global $post;

		/**
		 * Register the main, minified
		 * and compiled script file.
		 */
		wp_register_script( 'bm-event-app', Plugin::get_assets_url() . '/scripts/dist/app.js', [ 'jquery', 'select2' ], Plugin::get_version(), true );
		wp_register_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.0.0/dist/js/select2.min.js', [ 'jquery' ], '4.0.0', true );
		wp_register_script('liveviewer', Plugin::get_assets_url('/scripts/dist/live-viewer.js'), [], Plugin::get_version(), true);
		wp_register_script('time-switcher', Plugin::get_assets_url('/scripts/dist/time-switcher.js'), [], Plugin::get_version(), true);

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

		if(has_shortcode($post->post_content, 'bm_liveviewer')) {
			wp_enqueue_script( 'liveviewer' );

			wp_localize_script('liveviewer', 'BMELiveViewer', [
				'apiUrl' => rest_url('bm-events/v1'),
				'pollingInterval' => 30000
			]);
		}

		if(has_shortcode($post->post_content, 'bm_display_events')) {
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'time-switcher' );

			wp_localize_script('time-switcher', 'BMETimeSwitcher', [
				'apiUrl' => rest_url('bm-events/v1')
			]);
		}
	}

	/**
	 * Enqueue Scripts on admin side
	 *
	 * We want to allow the use of good script debugging here too,
	 * so be mindful and use the SCRIPTS_DEBUG constant
	 * to load both minified for production and non-minified files
	 * for testing purposes.
	 **/
	public static function admin_scripts() {

		/**
		 * Register the main, minified
		 * and compiled script file.
		 */
		wp_register_script( 'bm-event-admin', Plugin::get_assets_url() . '/scripts/dist/admin.js', [ 'jquery' ], Plugin::get_version(), true );

		// Enqueue.
		wp_enqueue_script( 'bm-event-admin' );

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

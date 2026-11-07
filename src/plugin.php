<?php
/**
 * Main plugin class.
 *
 * @package BernskioldMedia\WP\Event
 */

namespace BernskioldMedia\WP\Event;

use BernskioldMedia\WP\Event\Blocks\Track_Timetable\Track_Timetable_Block;
use BernskioldMedia\WP\Event\Data_Stores\Session;
use BernskioldMedia\WP\Event\Data_Stores\Speaker;
use BernskioldMedia\WP\Event\Data_Stores\Track;
use BernskioldMedia\WP\Event\Rest\NextVideo;
use BernskioldMedia\WP\Event\Rest\SessionGrid;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 *
 * @package BernskioldMedia\WP\Event
 */
class Plugin {

	public const TEXTDOMAIN = 'bm-event';

	/**
	 * Prefix used for the block names and script handles.
	 */
	public const BLOCK_PREFIX = 'bm';

	/**
	 * Version
	 *
	 * Kept as a property in this exact form because the release
	 * workflow bumps `version = '...'` in this file.
	 *
	 * @var string
	 */
	protected static $version = '1.2.4';

	/**
	 * Data Stores
	 *
	 * @var class-string<Abstracts\Data_Store_WP>[]
	 */
	public static $data_stores = [
		Session::class,
		Speaker::class,
		Track::class,
	];

	/**
	 * Classes whose hooks are registered when the plugin boots.
	 *
	 * @var class-string<Hookable>[]
	 */
	private const BOOT = [
		Assets::class,
		Shortcodes::class,
	];

	private static ?self $instance = null;

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public function __construct() {
		add_action( 'init', [ self::class, 'load_languages' ] );
		add_action( 'init', [ self::class, 'register_blocks' ], 99 );
		add_action( 'enqueue_block_editor_assets', [ self::class, 'block_editor_scripts' ], 99 );
		add_filter( 'block_categories', [ self::class, 'setup_block_categories' ] );

		foreach ( self::BOOT as $class ) {
			$class::hooks();
		}

		( new NextVideo() )->load();
		( new SessionGrid() )->load();

		do_action( 'bm_event_init' );

		foreach ( self::$data_stores as $data_store ) {
			$data_store::hooks();
		}

		require_once self::get_path( 'src/customizer.php' );

		do_action( 'bm_event_loaded' );
	}

	/**
	 * Cloning is forbidden.
	 */
	private function __clone() {
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		throw new \LogicException( 'Cannot unserialize a singleton.' );
	}

	public static function get_path( string $file = '' ): string {
		return untrailingslashit( plugin_dir_path( BM_EVENT_FILE_PATH ) ) . '/' . $file;
	}

	public static function get_url( string $file = '' ): string {
		return untrailingslashit( plugin_dir_url( BM_EVENT_FILE_PATH ) ) . '/' . $file;
	}

	public static function get_assets_url( string $file = '' ): string {
		return self::get_url( 'assets/' . $file );
	}

	public static function get_version(): string {
		return static::$version;
	}

	public static function get_textdomain(): string {
		return self::TEXTDOMAIN;
	}

	/**
	 * Load translations, first from the global languages
	 * directory and then from the plugin.
	 */
	public static function load_languages(): void {
		$textdomain = self::TEXTDOMAIN;
		$locale     = is_admin() ? get_user_locale() : get_locale();
		$locale     = apply_filters( 'plugin_locale', $locale, $textdomain );

		unload_textdomain( $textdomain );
		load_textdomain( $textdomain, WP_LANG_DIR . "/{$textdomain}/{$textdomain}-{$locale}.mo" );
		load_plugin_textdomain( $textdomain, false, dirname( plugin_basename( BM_EVENT_FILE_PATH ) ) . '/languages' );
	}

	/**
	 * Register the blocks.
	 */
	public static function register_blocks(): void {
		register_block_type( self::BLOCK_PREFIX . '/track-timetable', [
			'render_callback' => [ Track_Timetable_Block::class, 'render' ],
			'attributes'      => Track_Timetable_Block::get_attributes(),
		] );
	}

	/**
	 * Load the editor scripts for the blocks.
	 */
	public static function block_editor_scripts(): void {
		$handle = self::BLOCK_PREFIX . '-track-timetable';

		wp_enqueue_script( $handle, self::get_url( 'dist/track-timetable.js' ), [
			'wp-blocks',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-block-editor',
		], self::get_version(), false );

		wp_set_script_translations( $handle, self::TEXTDOMAIN, self::get_path( 'languages/' ) );
	}

	/**
	 * Setup Block Categories
	 *
	 * @param  array  $categories
	 *
	 * @return array
	 */
	public static function setup_block_categories( $categories ): array {
		return array_merge( $categories, [
			[
				'slug'  => 'event',
				'title' => __( 'Events', 'bm-events' ),
			],
		] );
	}

	/**
	 * Load Template
	 *
	 * @param  string  $template_name
	 * @param  array   $variables
	 */
	public static function load_template( $template_name, $variables = [] ): void {

		if ( $variables ) {
			extract( $variables ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		$located = locate_template( 'components/' . $template_name );

		if ( $located ) {
			include $located;
		} else {
			include self::get_path( 'views/' . $template_name . '.php' );
		}

	}
}

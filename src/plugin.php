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
		add_filter( 'block_categories_all', [ self::class, 'setup_block_categories' ] );

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
	 * Register the blocks from their block.json files.
	 *
	 * The block metadata is read from the manifest that
	 * `npm run build` generates.
	 */
	public static function register_blocks(): void {
		Assets::register_block_styles();

		wp_register_block_types_from_metadata_collection( self::get_path( 'blocks' ), self::get_path( 'blocks/blocks-manifest.php' ) );

		// Core sets up translations for block scripts from the global
		// languages directory only. Also look in the plugin.
		wp_set_script_translations( generate_block_asset_handle( Track_Timetable_Block::NAME, 'editorScript' ), self::TEXTDOMAIN, self::get_path( 'languages/' ) );

		add_filter( 'script_module_data_' . Track_Timetable_Block::get_view_module_id(), [ Track_Timetable_Block::class, 'view_module_data' ] );
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
				'title' => __( 'Events', 'bm-event' ),
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

		// Themes can override a template in components/<template>.php. The
		// extension used to be left out, so the override was never found.
		$located = locate_template( [ 'components/' . $template_name . '.php', 'components/' . $template_name ] );

		if ( $located ) {
			include $located;
		} else {
			include self::get_path( 'views/' . $template_name . '.php' );
		}

	}
}

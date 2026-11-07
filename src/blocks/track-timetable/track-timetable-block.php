<?php

namespace BernskioldMedia\WP\Event\Blocks\Track_Timetable;

use BernskioldMedia\WP\Event\Data_Stores;
use BernskioldMedia\WP\Event\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Track_Timetable_Block
 *
 * The block is registered from blocks/track-timetable/block.json and
 * rendered through its render.php, which calls render() below.
 *
 * @package BernskioldMedia\WP\Event\Blocks\Track_Timetable
 */
class Track_Timetable_Block {

	/**
	 * Block name.
	 */
	public const NAME = 'bm/track-timetable';

	/**
	 * Get the ID of the block's view script module.
	 */
	public static function get_view_module_id(): string {
		return generate_block_asset_handle( self::NAME, 'viewScriptModule' );
	}

	/**
	 * Data for the view script module (the timezone selector).
	 *
	 * @param  array $data
	 *
	 * @return array
	 */
	public static function view_module_data( array $data ): array {
		return array_merge( $data, [
			'texts'        => [
				'selectTz' => __( 'Select Your Timezone...', 'bm-event' ),
				'tzLabel'  => __( 'See the schedule in your local time.', 'bm-event' ),
			],
			'gmtOffset'    => get_option( 'gmt_offset' ),
			'siteTimezone' => get_option( 'timezone_string' ),
		] );
	}

	/**
	 * Get Posts Query
	 *
	 * @param  array  $attributes
	 *
	 * @return \WP_Query
	 */
	protected static function get_query( $attributes ): \WP_Query {

		$query_args = [
			'post_type'      => Data_Stores\Session::get_key(),
			'posts_per_page' => 100, // "Everything".
		];

		if ( ! empty( $attributes['track_id'] ) ) {
			$query_args['tax_query'][] = [
				'taxonomy' => Data_Stores\Track::get_key(),
				'terms'    => $attributes['track_id'],
				'fields'   => 'term_id',
			];
		}

		return new \WP_Query( $query_args );

	}

	/**
	 * Get style of the block.
	 *
	 * @param  array $attributes
	 *
	 * @return string Either "grid" or "list".
	 */
	public static function get_style( $attributes ): string {

		if ( ! isset( $attributes['className'] ) ) {
			return 'list';
		}

		if ( false !== strpos( $attributes['className'], 'is-style-grid' ) ) {
			return 'grid';
		}

		return 'list';

	}

	/**
	 * Render the content
	 *
	 * @param  array  $attributes
	 *
	 * @return string
	 */
	public static function render( array $attributes ): string {

		$posts = self::get_query( $attributes );

		if ( ! $posts->have_posts() ) {
			return '';
		}

		// The anchor support adds the id, and the custom class name support
		// any additional classes.
		$wrapper_attributes = [
			'class' => 'sessions is-style-' . self::get_style( $attributes ),
		];

		ob_start();
		?>

		<div <?php echo get_block_wrapper_attributes( $wrapper_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

			<?php if ( ! empty( $attributes['showTimezoneSelector'] ) ) : ?>
				<div class="track-timetable-timezone-selector js--track-timetable-timezone-selector"></div>
			<?php endif; ?>

			<?php while ( $posts->have_posts() ) : $posts->the_post(); // @codingStandardsIgnoreLine ?>
				<?php Plugin::load_template( 'event/item-' . self::get_style( $attributes ) ); ?>
			<?php endwhile; ?>

		</div>

		<?php
		wp_reset_postdata();

		return (string) ob_get_clean();

	}

}

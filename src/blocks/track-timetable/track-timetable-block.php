<?php

namespace BernskioldMedia\WP\Event\Blocks\Track_Timetable;

use BernskioldMedia\WP\Event\Data_Stores;
use BernskioldMedia\WP\Event\Plugin;

/**
 * Class Track_Timetable_Block
 *
 * @package BernskioldMedia\WP\Event\Blocks\Track_Timetable
 */
class Track_Timetable_Block {

	/**
	 * Since this is a dynamically generated block, we
	 * define the attributes here using PHP.
	 *
	 * @var array
	 */
	protected static array $attributes = [
		'track_id'             => [
			'type'    => 'number',
			'default' => 0,
		],
		'showTimezoneSelector' => [
			'type'    => 'boolean',
			'default' => false,
		],
	];

	/**
	 * Get the attributes to register the block with.
	 *
	 * @return array
	 */
	public static function get_attributes(): array {
		return static::$attributes;
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

		if ( $attributes['track_id'] ) {
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
	 */
	protected static function get_style( $attributes ) {

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
	 * @return false|string
	 */
	public static function render( array $attributes ): string {

		$posts = self::get_query( $attributes );

		$classes   = [ 'sessions' ];
		$classes[] = 'is-style-' . self::get_style( $attributes );
		$classes   = implode( ' ', $classes );

		ob_start();

		if ( $posts->have_posts() ) : ?>

			<div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( isset( $attributes['anchor'] ) ? $attributes['anchor'] : '' ); ?>">

				<?php if ( isset( $attributes['showTimezoneSelector'] ) && $attributes['showTimezoneSelector'] ) : ?>
					<div class="track-timetable-timezone-selector js--track-timetable-timezone-selector"></div>
				<?php endif; ?>

				<?php while ( $posts->have_posts() ) : $posts->the_post(); // @codingStandardsIgnoreLine ?>
					<?php Plugin::load_template( 'event/item-' . self::get_style( $attributes ) ); ?>
				<?php endwhile; ?>

			</div>

		<?php
		endif;

		return ob_get_clean();

	}

}

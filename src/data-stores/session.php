<?php
/**
 * Data Store for Session
 *
 * @author  Bernskiold Media <info@bernskioldmedia.com>
 * @package BernskioldMedia\WP\Event
 * @since   1.0.0
 */

namespace BernskioldMedia\WP\Event\Data_Stores;

use BernskioldMedia\WP\Event\Abstracts;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Class Session
 *
 * @package BernskioldMedia\WP\Event
 */
class Session extends Abstracts\Custom_Post_Type {

	/**
	 * Post Type Key
	 *
	 * @var string
	 */
	protected static $key = 'session';

	/**
	 * Plural Key
	 *
	 * @var string
	 */
	protected static $plural_key = 'sessions';

	/**
	 * Class name of the data class.
	 *
	 * @var string
	 */
	protected static $data_class = \BernskioldMedia\WP\Event\Data\Session::class;

	/**
	 * Array of metadata (field) keys.
	 *
	 * @var array
	 */
	public static $metadata = [
		'name',
		'topic',
		'short_description',
		'session_type',
		'date',
		'start_time',
		'end_time',
		'start_datetime',
		'end_datetime',
		'speakers',
	];

	/**
	 * Sites registered after this date (midnight UTC, 2022-07-01) store
	 * session times in the combined session_start_datetime and
	 * session_end_datetime fields. Older sites use the separate date,
	 * start time and end time fields.
	 */
	public const DATETIME_FIELDS_SINCE = 1656633600;

	/**
	 * Get when the current site was registered, as a timestamp.
	 *
	 * Only multisite networks record this. A single site has no
	 * registration date and gets the datetime fields. Filter
	 * `bm_event_site_registered_timestamp` to change this.
	 */
	public static function get_site_registered_timestamp(): int {
		$timestamp = PHP_INT_MAX;

		if ( is_multisite() ) {
			$site = get_site( get_current_blog_id() );

			if ( $site && $site->registered ) {
				$timestamp = (int) strtotime( $site->registered );
			}
		}

		return (int) apply_filters( 'bm_event_site_registered_timestamp', $timestamp );
	}

	/**
	 * Register Post Type
	 *
	 * @see https://codex.wordpress.org/Function_Reference/register_post_type
	 * @see https://codex.wordpress.org/Function_Reference/register_post_type#Parameters
	 */
	public static function register(): void {

		$labels = [
			'name'                  => _x( 'Sessions', 'Post Type General Name', 'bm-event' ),
			'singular_name'         => _x( 'Session', 'Post Type Singular Name', 'bm-event' ),
			'menu_name'             => __( 'Sessions', 'bm-event' ),
			'name_admin_bar'        => __( 'Sessions', 'bm-event' ),
			'archives'              => __( 'Sessions', 'bm-event' ),
			'parent_item_colon'     => __( 'Parent Session:', 'bm-event' ),
			'all_items'             => __( 'All Sessions', 'bm-event' ),
			'add_new_item'          => __( 'Add New Session', 'bm-event' ),
			'add_new'               => __( 'Add New', 'bm-event' ),
			'new_item'              => __( 'New Session', 'bm-event' ),
			'edit_item'             => __( 'Edit Session', 'bm-event' ),
			'update_item'           => __( 'Update Session', 'bm-event' ),
			'view_item'             => __( 'View Session', 'bm-event' ),
			'search_items'          => __( 'Search Session', 'bm-event' ),
			'not_found'             => __( 'Not found', 'bm-event' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'bm-event' ),
			'featured_image'        => __( 'Featured Image', 'bm-event' ),
			'set_featured_image'    => __( 'Set Featured Image', 'bm-event' ),
			'remove_featured_image' => __( 'Remove Featured Image', 'bm-event' ),
			'use_featured_image'    => __( 'Use as Featured Image', 'bm-event' ),
			'insert_into_item'      => __( 'Insert into session', 'bm-event' ),
			'uploaded_to_this_item' => __( 'Uploaded to this session', 'bm-event' ),
			'items_list'            => __( 'Sessions list', 'bm-event' ),
			'items_list_navigation' => __( 'Sessions list navigation', 'bm-event' ),
			'filter_items_list'     => __( 'Filter sessions list', 'bm-event' ),
			'attributes'            => __( 'Attributes', 'bm-event' ),
		];

		$supports = [
			'title',
			'editor',
			'revisions',
		];

		$args = [
			'label'               => __( 'Sessions', 'bm-event' ),
			'labels'              => $labels,
			'supports'            => $supports,
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true, // Set this to string to make sub-page.
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-schedule',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => _x( 'sessions', 'examples post type archive slug', 'bm-event' ),
			'rewrite'             => [
				'slug'       => _x( 'sessions', 'examples post type single slug', 'bm-event' ),
				'with_front' => false,
			],
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'delete_with_user'    => null,
			'show_in_rest'        => true, // Required for Gutenberg.
			'capabilities'        => self::get_capabilities(),
			'map_meta_cap'        => true,
		];

		register_post_type( self::get_key(), $args );

	}

	/**
	 * Modify the query.
	 *
	 * @param  WP_Query  $query
	 *
	 * @return WP_Query
	 */
	public static function query_modifications( $query ) {

		if ( is_admin() && ! $query->is_main_query() ) {
			return $query;
		}

		if ( $query->get( 'post_type' ) !== static::get_key() ) {
			return $query;
		}

		// Yes...we are really doing this to migrate to a new structure!
		if ( self::get_site_registered_timestamp() >= self::DATETIME_FIELDS_SINCE ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_type', 'DATETIME' );
			$query->set( 'meta_key', 'session_start_datetime' );
			$query->set( 'order', 'ASC' );
		} else {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_type', 'TIME' );
			$query->set( 'meta_key', 'session_start_time' );
			$query->set( 'order', 'ASC' );
		}

		return $query;

	}

	/**
	 * Meta Fields via Advanced Custom Fields
	 */
	public static function fields(): void {

		$registered = self::get_site_registered_timestamp();

		acf_add_local_field_group( [
			'key'                   => 'group_5ecd0f226b78d',
			'title'                 => __( 'Information', 'bm-event' ),
			'fields'                => [
				[
					'key'               => 'field_5ecd10d06e764',
					'label'             => __( 'Topic', 'bm-event' ),
					'name'              => 'session_topic',
					'type'              => 'text',
					'instructions'      => __( 'Optionally prepend the title with a topic which will be shown above in small text.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				],
				[
					'key'               => 'field_5ecd0f392473a',
					'label'             => __( 'Short Description', 'bm-event' ),
					'name'              => 'session_short_description',
					'type'              => 'textarea',
					'instructions'      => __( 'The main content will be shown on the single event. This short description will show in lists and session tables.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => '',
					'maxlength'         => '',
					'rows'              => 4,
					'new_lines'         => '',
				],
				[
					'key'               => 'field_5ecd10648a355',
					'label'             => __( 'Session Type', 'bm-event' ),
					'name'              => 'session_type',
					'type'              => 'radio',
					'instructions'      => __( 'The visual style on the page might differ depending on the session type.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'choices'           => [
						'session' => _x( 'Session', 'a type of session', 'bm-event' ),
						'break'   => _x( 'Break', 'a type of session', 'bm-event' ),
					],
					'allow_null'        => 0,
					'other_choice'      => 0,
					'default_value'     => 'session',
					'layout'            => 'horizontal',
					'return_format'     => 'array',
					'save_other_choice' => 0,
				],
			],
			'location'              => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => self::get_key(),
					],
				],
			],
			'menu_order'            => 0,
			'position'              => 'side',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => __( 'Information about a session.', 'bm-event' ),
		] );

		acf_add_local_field_group( [
			'key'                   => 'group_5ecd0f226b19a',
			'title'                 => __( 'Video', 'bm-event' ),
			'fields'                => [
				[
					'key'               => 'field_5ecd10d06e751',
					'label'             => __( 'Embed URL', 'bm-event' ),
					'name'              => 'session_live_url',
					'type'              => 'url',
					'instructions'      => __( 'Enter a YouTube or Vimeo video URL to a recording or livestream URL.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				],
			],
			'location'              => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => self::get_key(),
					],
				],
			],
			'menu_order'            => 0,
			'position'              => 'side',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => __( 'Recording or live event video.', 'bm-event' ),
		] );

		if ( $registered <= self::DATETIME_FIELDS_SINCE ) {
			acf_add_local_field_group([
				'key' => 'group_5ecd095bf2280',
				'title' => __('Date & Time', 'bm-event'),
				'fields' => [
					[
						'key' => 'field_5ecd0e50cafde',
						'label' => __('Date', 'bm-event'),
						'name' => 'session_date',
						'type' => 'date_picker',
						'instructions' => __('On which date will this session take place? Dates are used for calendar sync and multi-day tables.',
							'bm-event'),
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'display_format' => 'Y-m-d',
						'return_format' => 'Ymd',
						'first_day' => 1,
					],
					[
						'key' => 'field_5ecd0e68cafdf',
						'label' => __('Start Time', 'bm-event'),
						'name' => 'session_start_time',
						'type' => 'time_picker',
						'instructions' => __('Enter the time the session is supposed to start at.', 'bm-event'),
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'display_format' => 'H:i',
						'return_format' => 'H:i',
					],
					[
						'key' => 'field_5ecd0e89cafe0',
						'label' => __('End Time', 'bm-event'),
						'name' => 'session_end_time',
						'type' => 'time_picker',
						'instructions' => __('Setting an end time for the session is optional but recommended as the information is helpful to the visitor and is used in the calendar sync.',
							'bm-event'),
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'display_format' => 'H:i',
						'return_format' => 'H:i',
					],
				],
				'location' => [
					[
						[
							'param' => 'post_type',
							'operator' => '==',
							'value' => self::get_key(),
						],
					],
				],
				'menu_order' => 5,
				'position' => 'side',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'field',
				'hide_on_screen' => '',
				'active' => true,
				'description' => __('Information about the session date and time.', 'bm-event'),
			]);
		}

		if ( $registered > self::DATETIME_FIELDS_SINCE ) {

			acf_add_local_field_group([
				'key' => 'group_637238a0eca63',
				'title' => __('Date & Time', 'bm-event'),
				'fields' => [
					[
						'key' => 'field_637238add4319',
						'label' => __('Start Date and Time', 'bm-event'),
						'name' => 'session_start_datetime',
						'type' => 'date_time_picker',
						'instructions' => __('On which date and time will this session take place?', 'bm-event'),
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'display_format' => 'Y-m-d H:i:s',
						'return_format' => 'Y-m-d H:i:s',
						'first_day' => 1,
					],
					[
						'key' => 'field_637238fd1b720',
						'label' => __('End Date and Time', 'bm-event'),
						'name' => 'session_end_datetime',
						'type' => 'date_time_picker',
						'instructions' => __('Setting an end time for the session is optional but recommended as the information is helpful to the visitor and is used in the calendar sync.',
							'bm-event'),
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => [
							'width' => '',
							'class' => '',
							'id' => '',
						],
						'display_format' => 'Y-m-d H:i:s',
						'return_format' => 'Y-m-d H:i:s',
						'first_day' => 1,
					],
				],
				'location' => [
					[
						[
							'param' => 'post_type',
							'operator' => '==',
							'value' => self::get_key(),
						],
					],
				],
				'menu_order' => 5,
				'position' => 'side',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'field',
				'hide_on_screen' => '',
				'active' => true,
				'description' => __('Information about the session date and time.', 'bm-event'),
			]);
		}

		acf_add_local_field_group( [
			'key'                   => 'group_5ecd0e94b7e60',
			'title'                 => __( 'Speaker(s)', 'bm-event' ),
			'fields'                => [
				[
					'key'               => 'field_5ecd0e9a7f672',
					'label'             => __( 'Select Speaker(s)', 'bm-event' ),
					'name'              => 'session_speakers_ids',
					'type'              => 'post_object',
					'instructions'      => __( 'Select one or more speakers that are hosting this session. Their information is automatically retrieved from their speaker entry. Create a speaker first under Speakers.',
						'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'post_type'         => Speaker::get_key(),
					'taxonomy'          => '',
					'allow_null'        => 0,
					'multiple'          => 1,
					'return_format'     => 'id',
					'ui'                => 1,
				],
			],
			'location'              => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => self::get_key(),
					],
				],
			],
			'menu_order'            => 10,
			'position'              => 'side',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => __( 'Select speakers for sessions.', 'bm-event' ),
		] );

	}

}

<?php
/**
 * Data Store for Speaker
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
 * Class Speaker
 *
 * @package BernskioldMedia\WP\Event
 */
class Speaker extends Abstracts\Custom_Post_Type {

	/**
	 * Post Type Key
	 *
	 * @var string
	 */
	protected static $key = 'speaker';

	/**
	 * Plural Key
	 *
	 * @var string
	 */
	protected static $plural_key = 'speakers';

	/**
	 * @var string[]
	 */
	public static $metadata = [
		'name',
		'first_name',
		'last_name',
	];

	/**
	 * Register Post Type
	 *
	 * @see https://codex.wordpress.org/Function_Reference/register_post_type
	 * @see https://codex.wordpress.org/Function_Reference/register_post_type#Parameters
	 */
	public static function register(): void {

		$labels = [
			'name'                  => _x( 'Speakers', 'Post Type General Name', 'bm-event' ),
			'singular_name'         => _x( 'Speaker', 'Post Type Singular Name', 'bm-event' ),
			'menu_name'             => __( 'Speakers', 'bm-event' ),
			'name_admin_bar'        => __( 'Speakers', 'bm-event' ),
			'archives'              => __( 'Speakers', 'bm-event' ),
			'parent_item_colon'     => __( 'Parent Speaker:', 'bm-event' ),
			'all_items'             => __( 'All Speakers', 'bm-event' ),
			'add_new_item'          => __( 'Add New Speaker', 'bm-event' ),
			'add_new'               => __( 'Add New', 'bm-event' ),
			'new_item'              => __( 'New Speaker', 'bm-event' ),
			'edit_item'             => __( 'Edit Speaker', 'bm-event' ),
			'update_item'           => __( 'Update Speaker', 'bm-event' ),
			'view_item'             => __( 'View Speaker', 'bm-event' ),
			'search_items'          => __( 'Search Speaker', 'bm-event' ),
			'not_found'             => __( 'Not found', 'bm-event' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'bm-event' ),
			'featured_image'        => __( 'Photo', 'bm-event' ),
			'set_featured_image'    => __( 'Set Photo', 'bm-event' ),
			'remove_featured_image' => __( 'Remove Photo', 'bm-event' ),
			'use_featured_image'    => __( 'Use as Photo', 'bm-event' ),
			'insert_into_item'      => __( 'Insert into speaker', 'bm-event' ),
			'uploaded_to_this_item' => __( 'Uploaded to this speaker', 'bm-event' ),
			'items_list'            => __( 'Speakers list', 'bm-event' ),
			'items_list_navigation' => __( 'Speakers list navigation', 'bm-event' ),
			'filter_items_list'     => __( 'Filter speakers list', 'bm-event' ),
			'attributes'            => __( 'Attributes', 'bm-event' ),
		];

		$supports = [
			'title',
			'editor',
			'revisions',
			'thumbnail',
		];

		$args = [
			'label'               => __( 'Speakers', 'bm-event' ),
			'labels'              => $labels,
			'supports'            => $supports,
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true, // Set this to string to make sub-page.
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-id',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => _x( 'speakers', 'speaker post type archive slug', 'bm-event' ),
			'rewrite'             => [
				'slug'       => _x( 'speakers', 'speaker post type single slug', 'bm-event' ),
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
	 * Modify the QUery
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

		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', 'speaker_last_name' );
		$query->set( 'order', 'ASC' );

		return $query;

	}

	/**
	 * Meta Fields via Advanced Custom Fields
	 */
	public static function fields() {

		acf_add_local_field_group( [
			'key'                   => 'group_5ecd10dd8ae8c',
			'title'                 => __( 'Speaker Information', 'bm-event' ),
			'fields'                => [
				[
					'key'               => 'field_5ecd10ed0934b',
					'label'             => __( 'First Name', 'bm-event' ),
					'name'              => 'speaker_first_name',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 1,
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
					'key'               => 'field_5ecd10f30934c',
					'label'             => __( 'Last Name', 'bm-event' ),
					'name'              => 'speaker_last_name',
					'type'              => 'text',
					'instructions'      => '',
					'required'          => 1,
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
					'key'               => 'field_5ecd10fc0934d',
					'label'             => __( 'Company', 'bm-event' ),
					'name'              => 'speaker_company',
					'type'              => 'text',
					'instructions'      => '',
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
					'key'               => 'field_5ecd11020934e',
					'label'             => __( 'Title / Role', 'bm-event' ),
					'name'              => 'speaker_title',
					'type'              => 'text',
					'instructions'      => '',
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
					'key'               => 'field_5ecd11150934f',
					'label'             => __( 'Short Biography', 'bm-event' ),
					'name'              => 'speaker_short_biography',
					'type'              => 'textarea',
					'instructions'      => __( 'The main content will be shown on the speaker page, the short description can be shown in grids and lists and on sessions.',
						'bm-event' ),
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
					'rows'              => 3,
					'new_lines'         => '',
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
			'description'           => __( 'Information about the speaker.', 'bm-event' ),
		] );

		acf_add_local_field_group( [
			'key'                   => 'group_5ecd11333e809',
			'title'                 => __( 'Contact & Social Media', 'bm-event' ),
			'fields'                => [
				[
					'key'               => 'field_5ecd113fc2946',
					'label'             => __( 'Website URL', 'bm-event' ),
					'name'              => 'speaker_website_url',
					'type'              => 'url',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => 'https://',
				],
				[
					'key'               => 'field_5ecd114ac2947',
					'label'             => __( 'Twitter Username', 'bm-event' ),
					'name'              => 'speaker_twitter_handle',
					'type'              => 'text',
					'instructions'      => __( 'Please enter the username without the @. The link to the profile will be automatically created.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '@',
					'append'            => '',
					'maxlength'         => '',
				],
				[
					'key'               => 'field_5ecd1165c2948',
					'label'             => __( 'Facebook Page URL', 'bm-event' ),
					'name'              => 'speaker_facebook_url',
					'type'              => 'url',
					'instructions'      => __( 'Please enter the full URL to the speaker\'s Facebook page.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => 'https://facebook.com/*******',
				],
				[
					'key'               => 'field_5ecd1176c2949',
					'label'             => __( 'Instagram Username', 'bm-event' ),
					'name'              => 'speaker_instagram_username',
					'type'              => 'text',
					'instructions'      => __( 'Please enter only the username without the @. The link will be automatically created.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '@',
					'append'            => '',
					'maxlength'         => '',
				],
				[
					'key'               => 'field_5ecd11b98eeb7',
					'label'             => __( 'YouTube Channel URL', 'bm-event' ),
					'name'              => 'speaker_youtube_url',
					'type'              => 'url',
					'instructions'      => __( 'Please enter the full URL to the speaker\'s YouTube channel.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => 'https://www.youtube.com/c/*****',
				],
				[
					'key'               => 'field_5ecd11e174dd5',
					'label'             => __( 'LinkedIn URL', 'bm-event' ),
					'name'              => 'speaker_linkedin_url',
					'type'              => 'url',
					'instructions'      => __( 'Please enter the full URL to the speaker\'s LinkedIn profile.', 'bm-event' ),
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => [
						'width' => '',
						'class' => '',
						'id'    => '',
					],
					'default_value'     => '',
					'placeholder'       => 'https://linkedin.com/*****',
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
			'menu_order'            => 5,
			'position'              => 'side',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => __( 'Contact and social media details for speakers.', 'bm-event' ),
		] );

	}

}

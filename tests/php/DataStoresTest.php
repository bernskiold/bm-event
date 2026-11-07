<?php

namespace BernskioldMedia\WP\Event\Tests;

use BernskioldMedia\WP\Event\Data_Stores\Session;
use BernskioldMedia\WP\Event\Data_Stores\Speaker;
use BernskioldMedia\WP\Event\Data_Stores\Track;
use BernskioldMedia\WP\Event\Exceptions\Data_Store_Exception;
use BernskioldMedia\WP\Event\Install;

/**
 * @covers \BernskioldMedia\WP\Event\Data_Stores\Session
 * @covers \BernskioldMedia\WP\Event\Data_Stores\Speaker
 * @covers \BernskioldMedia\WP\Event\Data_Stores\Track
 * @covers \BernskioldMedia\WP\Event\Abstracts\Custom_Post_Type
 * @covers \BernskioldMedia\WP\Event\Abstracts\Taxonomy
 * @covers \BernskioldMedia\WP\Event\Install
 */
class DataStoresTest extends TestCase {

	public function test_session_post_type_is_registered(): void {
		$type = get_post_type_object( 'session' );

		$this->assertNotNull( $type );
		$this->assertTrue( $type->public );
		$this->assertTrue( $type->show_in_rest );
		$this->assertSame( 'sessions', $type->rewrite['slug'] );
		$this->assertSame( 'dashicons-schedule', $type->menu_icon );
		$this->assertTrue( post_type_supports( 'session', 'editor' ) );
		$this->assertTrue( post_type_supports( 'session', 'revisions' ) );
		$this->assertFalse( post_type_supports( 'session', 'thumbnail' ) );
	}

	public function test_speaker_post_type_is_registered(): void {
		$type = get_post_type_object( 'speaker' );

		$this->assertNotNull( $type );
		$this->assertTrue( $type->public );
		$this->assertTrue( $type->show_in_rest );
		$this->assertSame( 'speakers', $type->rewrite['slug'] );
		$this->assertTrue( post_type_supports( 'speaker', 'thumbnail' ) );
	}

	public function test_post_types_use_their_own_capabilities(): void {
		$caps = get_post_type_object( 'session' )->cap;

		$this->assertSame( 'edit_session', $caps->edit_post );
		$this->assertSame( 'edit_sessions', $caps->edit_posts );
		$this->assertSame( 'edit_others_sessions', $caps->edit_others_posts );
		$this->assertSame( 'publish_sessions', $caps->publish_posts );
		$this->assertSame( 'edit_sessions', $caps->create_posts );
		$this->assertSame( 'edit_speakers', get_post_type_object( 'speaker' )->cap->edit_posts );
	}

	public function test_track_taxonomy_is_registered_for_sessions(): void {
		$taxonomy = get_taxonomy( 'track' );

		$this->assertNotFalse( $taxonomy );
		$this->assertTrue( $taxonomy->hierarchical );
		$this->assertTrue( $taxonomy->show_in_rest );
		$this->assertSame( [ 'session' ], $taxonomy->object_type );
		$this->assertSame( 'tracks', $taxonomy->rewrite['slug'] );
		$this->assertSame( 'manage_tracks', $taxonomy->cap->manage_terms );
		$this->assertSame( 'assign_tracks', $taxonomy->cap->assign_terms );
	}

	public function test_install_grants_the_default_capabilities(): void {
		Install::install();

		$administrator = get_role( 'administrator' );
		$editor        = get_role( 'editor' );
		$author        = get_role( 'author' );
		$contributor   = get_role( 'contributor' );

		$this->assertTrue( $administrator->has_cap( 'edit_sessions' ) );
		$this->assertTrue( $administrator->has_cap( 'publish_speakers' ) );
		$this->assertTrue( $administrator->has_cap( 'manage_tracks' ) );

		$this->assertTrue( $editor->has_cap( 'delete_others_sessions' ) );
		$this->assertTrue( $editor->has_cap( 'delete_tracks' ) );

		$this->assertTrue( $author->has_cap( 'publish_sessions' ) );
		$this->assertFalse( $author->has_cap( 'edit_others_sessions' ) );
		$this->assertFalse( $author->has_cap( 'delete_tracks' ) );

		$this->assertTrue( $contributor->has_cap( 'edit_sessions' ) );
		$this->assertFalse( $contributor->has_cap( 'publish_sessions' ) );
		$this->assertTrue( $contributor->has_cap( 'assign_tracks' ) );
	}

	public function test_an_administrator_can_edit_sessions_after_install(): void {
		Install::install();

		$admin   = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$session = $this->create_session();

		$this->assertTrue( user_can( $admin, 'edit_post', $session ) );
		$this->assertTrue( user_can( $admin, 'edit_sessions' ) );
	}

	public function test_install_registers_the_activation_hook(): void {
		$this->assertNotFalse( has_action( 'activate_' . plugin_basename( BM_EVENT_FILE_PATH ), [ Install::class, 'install' ] ) );
	}

	public function test_create_update_and_find_a_session(): void {
		$id = Session::create( 'Opening Keynote' );

		$this->assertSame( 'publish', get_post_status( $id ) );
		$this->assertSame( 'session', get_post_type( $id ) );
		$this->assertSame( $id, Session::does_object_exist( 'Opening Keynote' ) );
		$this->assertNull( Session::does_object_exist( 'Does not exist' ) );

		Session::update( $id, [ 'post_title' => 'Closing Keynote' ] );

		$this->assertSame( 'Closing Keynote', get_the_title( $id ) );
		$this->assertTrue( Session::delete( $id, true ) );
		$this->assertNull( get_post( $id ) );
	}

	public function test_creating_without_a_name_throws(): void {
		// The exception logs the error; keep it out of the test output.
		$log = ini_set( 'error_log', '/dev/null' );

		try {
			Session::create( '' );
			$this->fail( 'Expected a Data_Store_Exception.' );
		} catch ( Data_Store_Exception $e ) {
			$this->assertSame( [], $e->get_data() );
		} finally {
			ini_set( 'error_log', (string) $log );
		}
	}

	public function test_create_update_and_delete_a_track(): void {
		$id = Track::create( 'Workshops' );

		$this->assertSame( $id, Track::does_object_exist( 'Workshops' ) );

		// Creating an existing term updates it instead.
		$this->assertSame( $id, Track::create( 'Workshops', [ 'description' => 'Hands-on' ] ) );
		$this->assertSame( 'Hands-on', get_term( $id, 'track' )->description );

		$this->assertTrue( Track::delete( $id ) );
		$this->assertNull( Track::does_object_exist( 'Workshops' ) );
	}

	public function test_sessions_are_ordered_by_start_datetime(): void {
		$later   = $this->create_session( [ 'session_start_datetime' => '2030-05-02 09:00:00' ], [ 'post_title' => 'Later' ] );
		$earlier = $this->create_session( [ 'session_start_datetime' => '2030-05-01 09:00:00' ], [ 'post_title' => 'Earlier' ] );

		$query = new \WP_Query( [ 'post_type' => 'session' ] );

		$this->assertSame( [ $earlier, $later ], wp_list_pluck( $query->posts, 'ID' ) );
	}

	public function test_old_sites_order_sessions_by_start_time(): void {
		add_filter( 'bm_event_site_registered_timestamp', fn() => strtotime( '2021-01-01' ) );

		$later   = $this->create_session( [ 'session_start_time' => '14:00' ] );
		$earlier = $this->create_session( [ 'session_start_time' => '09:00' ] );

		$query = new \WP_Query( [ 'post_type' => 'session' ] );

		$this->assertSame( 'session_start_time', $query->get( 'meta_key' ) );
		$this->assertSame( [ $earlier, $later ], wp_list_pluck( $query->posts, 'ID' ) );
	}

	public function test_speakers_are_ordered_by_last_name(): void {
		$smith   = $this->create_speaker( [ 'speaker_first_name' => 'Ann', 'speaker_last_name' => 'Smith' ] );
		$andrews = $this->create_speaker( [ 'speaker_first_name' => 'Bo', 'speaker_last_name' => 'Andrews' ] );

		$query = new \WP_Query( [ 'post_type' => 'speaker' ] );

		$this->assertSame( [ $andrews, $smith ], wp_list_pluck( $query->posts, 'ID' ) );
	}

	public function test_single_sites_get_the_datetime_fields(): void {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Single site only.' );
		}

		$this->assertSame( PHP_INT_MAX, Session::get_site_registered_timestamp() );
		$this->assertNotEmpty( acf_get_local_field_group( 'group_637238a0eca63' ) );
		$this->assertEmpty( acf_get_local_field_group( 'group_5ecd095bf2280' ) );
	}

	public function test_registers_the_field_groups(): void {
		foreach ( [ 'group_5ecd0f226b78d', 'group_5ecd0f226b19a', 'group_5ecd0e94b7e60' ] as $key ) {
			$this->assertNotEmpty( acf_get_local_field_group( $key ), $key );
		}

		$this->assertNotEmpty( acf_get_local_field( 'field_5ecd10d06e764' ) );
	}

}

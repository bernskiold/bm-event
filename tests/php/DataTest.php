<?php

namespace BernskioldMedia\WP\Event\Tests;

use BernskioldMedia\WP\Event\Data\Session;
use BernskioldMedia\WP\Event\Data\Speaker;
use BernskioldMedia\WP\Event\SessionHelper;

/**
 * @covers \BernskioldMedia\WP\Event\Data\Session
 * @covers \BernskioldMedia\WP\Event\Data\Speaker
 * @covers \BernskioldMedia\WP\Event\Abstracts\Data
 * @covers \BernskioldMedia\WP\Event\SessionHelper
 */
class DataTest extends TestCase {

	public function test_session_reads_its_fields(): void {
		$id = $this->create_session( [
			'session_topic'             => 'Keynote',
			'session_short_description' => 'Welcome to the event.',
			'session_type'              => 'break',
			'session_start_datetime'    => '2030-05-01 09:30:00',
			'session_end_datetime'      => '2030-05-01 10:15:00',
			'session_live_url'          => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
		], [ 'post_title' => 'Opening' ] );

		$session = new Session( $id );

		$this->assertSame( $id, $session->get_id() );
		$this->assertSame( 'Opening', $session->get_title() );
		$this->assertSame( 'Keynote', $session->get_topic() );
		$this->assertSame( 'Welcome to the event.', $session->get_short_description() );
		$this->assertSame( 'break', $session->get_session_type_key() );
		$this->assertSame( 'Break', $session->get_session_type_label() );
		$this->assertSame( '2030-05-01', $session->get_start_date_from_datetime() );
		$this->assertSame( '09:30', $session->get_start_time_from_datetime() );
		$this->assertSame( '10:15', $session->get_end_time_from_datetime() );
		$this->assertSame( '2030-05-01 09:30:00', $session->get_start_datetime() );
		$this->assertSame( 'dQw4w9WgXcQ', $session->get_youtube_id() );
		$this->assertSame( 'session', Session::get_object_type() );
	}

	public function test_empty_session_fields_are_null(): void {
		$session = new Session( $this->create_session() );

		$this->assertNull( $session->get_topic() ?: null );
		$this->assertNull( $session->get_start_time() );
		$this->assertNull( $session->get_start_datetime() );
		$this->assertNull( $session->get_speakers() );
		$this->assertNull( $session->get_youtube_id() );
	}

	public function test_legacy_date_and_time_fields(): void {
		$id = $this->create_session( [
			'session_date'       => '20300501',
			'session_start_time' => '09:00',
			'session_end_time'   => '10:00',
		] );

		$session = new Session( $id );

		$this->assertSame( '2030-05-01', $session->get_date() );
		$this->assertSame( '01/05/2030', $session->get_date( 'd/m/Y' ) );
		$this->assertSame( '09:00', $session->get_start_time() );
		$this->assertSame( '10:00', $session->get_end_time() );
	}

	public function test_speaker_reads_its_fields(): void {
		$id = $this->create_speaker( [
			'speaker_first_name'   => 'Ada',
			'speaker_last_name'    => 'Lovelace',
			'speaker_company'      => 'Analytical Engines',
			'speaker_title'        => 'Programmer',
			'speaker_linkedin_url' => 'https://www.linkedin.com/in/ada',
		] );

		$speaker = new Speaker( $id );

		$this->assertSame( 'Ada Lovelace', $speaker->get_name() );
		$this->assertSame( 'Analytical Engines', $speaker->get_company() );
		$this->assertSame( 'Programmer', $speaker->get_title() );
		$this->assertSame( 'https://www.linkedin.com/in/ada', $speaker->get_linkedin_url() );
		$this->assertSame( [
			'name'       => 'Ada Lovelace',
			'first_name' => 'Ada',
			'last_name'  => 'Lovelace',
		], $speaker->to_array() );
	}

	public function test_session_speakers(): void {
		$ada   = $this->create_speaker( [ 'speaker_first_name' => 'Ada', 'speaker_last_name' => 'Lovelace' ] );
		$grace = $this->create_speaker( [ 'speaker_first_name' => 'Grace', 'speaker_last_name' => 'Hopper' ] );

		$session = new Session( $this->create_session( [ 'session_speakers_ids' => [ $ada, $grace ] ] ) );

		$this->assertSame( [ $ada, $grace ], array_map( 'intval', $session->get_speaker_ids() ) );
		$this->assertSame( [ 'Ada Lovelace', 'Grace Hopper' ], wp_list_pluck( $session->get_speakers(), 'name' ) );

		$objects = $session->get_speakers( OBJECT_K );
		$this->assertContainsOnlyInstancesOf( Speaker::class, $objects );
		$this->assertSame( $grace, $objects[1]->get_id() );
	}

	public function test_find_and_find_or_create(): void {
		$id = $this->create_session( [], [ 'post_title' => 'Workshop' ] );

		$this->assertSame( $id, Session::find( 'Workshop' )->get_id() );
		$this->assertNull( Session::find( 'Missing' ) );

		$created = Session::find_or_create( 'Panel' );
		$this->assertSame( 'Panel', get_the_title( $created->get_id() ) );
		$this->assertSame( $created->get_id(), Session::find_or_create( 'Panel' )->get_id() );
	}

	public function test_get_all_applies_the_arguments(): void {
		$first  = $this->create_session( [ 'session_start_datetime' => '2030-05-01 09:00:00' ] );
		$second = $this->create_session( [ 'session_start_datetime' => '2030-05-01 11:00:00' ] );

		$this->assertSame( [ $first, $second ], wp_list_pluck( Session::get_all(), 'ID' ) );
		$this->assertSame( [ $first ], wp_list_pluck( Session::get_all( [ 'posts_per_page' => 1 ] ), 'ID' ) );
	}

	public function test_serialising_keeps_only_the_id(): void {
		$id      = $this->create_session();
		$session = unserialize( serialize( new Session( $id ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

		$this->assertSame( $id, $session->get_id() );
	}

	public function test_user_permissions(): void {
		$session = new Session( $this->create_session() );

		$this->assertFalse( $session->can_user( 'edit', 0 ) );

		$admin = self::factory()->user->create( [ 'role' => 'administrator' ] );
		get_role( 'administrator' )->add_cap( 'edit_sessions' );
		get_role( 'administrator' )->add_cap( 'edit_others_sessions' );
		get_role( 'administrator' )->add_cap( 'edit_published_posts' );

		$this->assertTrue( $session->can_user( 'edit', $admin ) );
	}

	public function test_session_helper(): void {
		$id = $this->create_session( [
			'session_start_datetime' => '2030-05-01 09:30:00',
			'session_end_datetime'   => '2030-05-01 10:15:00',
			'session_live_url'       => 'https://vimeo.com/123',
		] );

		$this->assertSame( '2030-05-01 09:30:00', SessionHelper::get_start_time( $id ) );
		$this->assertSame( '2030-05-01 10:15:00', SessionHelper::get_end_time( $id ) );
		$this->assertSame( strtotime( '2030-05-01 09:30:00' ), SessionHelper::get_start_timestamp( $id ) );
		$this->assertSame( 'https://vimeo.com/123', SessionHelper::get_embed_url( $id ) );
		$this->assertSame( 300, SessionHelper::get_seconds_before_to_load_next() );

		add_filter( 'bmevents_seconds_before_to_load_next', fn() => 60 );
		$this->assertSame( 60, SessionHelper::get_seconds_before_to_load_next() );
	}

}

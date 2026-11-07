<?php

namespace BernskioldMedia\WP\Event\Tests;

use BernskioldMedia\WP\Event\Log;

/**
 * @covers \BernskioldMedia\WP\Event\Log
 */
class LogTest extends TestCase {

	private string $log_file;

	private string $previous_log;

	public function set_up(): void {
		parent::set_up();

		$this->log_file     = wp_tempnam( 'bm-event-log' );
		$this->previous_log = (string) ini_set( 'error_log', $this->log_file );
	}

	public function tear_down(): void {
		ini_set( 'error_log', $this->previous_log );
		unlink( $this->log_file );

		parent::tear_down();
	}

	public function test_only_errors_and_above_are_logged_by_default(): void {
		if ( defined( 'LOGGING_LEVEL' ) || defined( 'ENABLE_LOGGING' ) ) {
			$this->markTestSkipped( 'Logging is configured for this run.' );
		}

		Log::info( 'Just so you know' );
		Log::error( 'Something broke', [ 'id' => 5 ] );

		$log = file_get_contents( $this->log_file );

		$this->assertStringNotContainsString( 'Just so you know', $log );
		$this->assertStringContainsString( '[bm-event] ERROR: Something broke {"id":5}', $log );
		$this->assertSame( Log::LEVELS['error'], Log::get_threshold() );
	}

}

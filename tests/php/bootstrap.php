<?php
/**
 * PHPUnit bootstrap for the WordPress integration test suite.
 *
 * Runs against a real WordPress through the WordPress test library.
 * Install it with `bin/install-wp-tests.sh`, export the variables it
 * prints and run `composer test`.
 *
 * The plugin reads and registers its fields through Advanced Custom
 * Fields, so the free ACF plugin is loaded too. Point ACF_PLUGIN_DIR at
 * an ACF checkout, or let the bootstrap download it from wordpress.org.
 *
 * @package BernskioldMedia\WP\Event
 */

// Locate the WordPress test library.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
}

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Point the WP test suite at the Yoast PHPUnit polyfills bundled via Composer.
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	$_polyfills = dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills';

	if ( is_dir( $_polyfills ) ) {
		define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_polyfills );
	}
}

$_functions = "{$_tests_dir}/includes/functions.php";

if ( ! file_exists( $_functions ) ) {
	echo "Could not find the WordPress test library at {$_tests_dir}." . PHP_EOL;
	echo 'Run bin/install-wp-tests.sh and export the variables it prints.' . PHP_EOL;
	exit( 1 );
}

/**
 * Find Advanced Custom Fields, downloading it when needed.
 */
function _bm_event_acf_file(): string {
	$dir = getenv( 'ACF_PLUGIN_DIR' );

	if ( ! $dir ) {
		// The same place bin/install-wp-tests.sh puts it.
		$target = getenv( 'WP_TESTS_INSTALL_DIR' ) ?: rtrim( sys_get_temp_dir(), '/\\' ) . '/bm-event-wp-tests';
		$dir    = $target . '/advanced-custom-fields';
	}

	$file = rtrim( $dir, '/\\' ) . '/acf.php';

	if ( file_exists( $file ) ) {
		return $file;
	}

	$parent = dirname( $dir );
	$zip    = $parent . '/advanced-custom-fields.zip';

	if ( ! is_dir( $parent ) ) {
		mkdir( $parent, 0777, true );
	}

	echo 'Downloading Advanced Custom Fields for the tests...' . PHP_EOL;

	$command = sprintf(
		'curl -fsSL -o %1$s %2$s && unzip -q -o %1$s -d %3$s',
		escapeshellarg( $zip ),
		escapeshellarg( 'https://downloads.wordpress.org/plugin/advanced-custom-fields.latest-stable.zip' ),
		escapeshellarg( $parent )
	);

	exec( $command, $output, $status ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec

	if ( 0 !== $status || ! file_exists( $file ) ) {
		echo "Could not find or download Advanced Custom Fields at {$dir}. Set ACF_PLUGIN_DIR." . PHP_EOL;
		exit( 1 );
	}

	return $file;
}

// PHPUnit includes this file inside a function, so share the path through a constant.
define( 'BM_EVENT_TESTS_ACF_FILE', _bm_event_acf_file() );

// Give access to tests_add_filter() before WordPress loads.
require_once $_functions;

/**
 * Manually load ACF and the plugin under test.
 */
function _manually_load_bm_event(): void {
	require BM_EVENT_TESTS_ACF_FILE;
	require dirname( __DIR__, 2 ) . '/bm-event.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_bm_event' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";

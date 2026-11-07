<?php
/**
 * Abstract REST API Endpoint Class
 *
 * @author  Bernskiold Media <info@bernskioldmedia.com>
 * @package BernskioldMedia\WP\Event
 * @since   1.0.0
 */

namespace BernskioldMedia\WP\Event\Abstracts;

use BernskioldMedia\WP\Event\Interfaces\REST_Endpoint_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class REST_Endpoint
 *
 * @package BernskioldMedia\WP\Event
 */
abstract class REST_Endpoint extends \WP_REST_Controller implements REST_Endpoint_Interface {

	/**
	 * Alias for GET method.
	 */
	const READABLE = 'GET';

	/**
	 * Alias for POST method.
	 */
	const CREATABLE = 'POST';

	/**
	 * Alias for PUT, POST, PATCH method.
	 */
	const EDITABLE = 'PUT, POST, PATCH';

	/**
	 * Alias for DELETE method
	 */
	const DELETABLE = 'DELETE';

	/**
	 * Alias for all methods together.
	 */
	const ALL = 'GET, POST, PUT, PATCH, DELETE';

	/**
	 * Namespace
	 *
	 * @var string
	 */
	protected $namespace = 'bm_event';

	/**
	 * Version
	 */
	protected string $version = '1';

	/**
	 * REST Routes
	 */
	protected array $routes = [];

	/**
	 * Set up the routes and hook them in.
	 */
	public function load(): void {
		$this->setup_routes();
		$this->init();
	}

	/**
	 * Initialize the endpoint.
	 */
	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register the routes.
	 */
	abstract protected function setup_routes(): void;

	/**
	 * Register REST Routes
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_rest_route/
	 */
	public function register_routes(): void {
		foreach ( $this->get_routes() as $route => $args ) {
			register_rest_route( $this->get_namespace(), $route, $args );
		}
	}

	/**
	 * Get sanitized URL param filter value.
	 */
	protected function get_filter_value( \WP_REST_Request $request, string $key ): ?string {
		return isset( $request[ $key ] ) ? wp_strip_all_tags( $request[ $key ] ) : null;
	}

	/**
	 * Add a route
	 *
	 * @param  string $route  NB. Prefix with /
	 * @param  array  $args
	 *
	 * @return static
	 */
	protected function add_route( string $route, array $args ) {
		$this->routes[ $route ] = $args;

		return $this;
	}

	/**
	 * Get Endpoint Routes
	 *
	 * @return array
	 */
	protected function get_routes(): array {
		return $this->routes;
	}

	/**
	 * Get the Namespace
	 *
	 * @return string
	 */
	protected function get_namespace(): string {
		return $this->namespace . '/v' . $this->version;
	}

	/**
	 * Public level permissions access.
	 *
	 * @return bool
	 */
	public function has_public_access(): bool {
		return true;
	}

}

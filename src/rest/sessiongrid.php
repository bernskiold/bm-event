<?php

namespace BernskioldMedia\WP\Event\Rest;

use BernskioldMedia\WP\Event\Plugin;
use BernskioldMedia\WP\Event\Abstracts\REST_Endpoint;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

class SessionGrid extends REST_Endpoint {

	protected $namespace = 'bm-events';

	protected function setup_routes(): void {
		$this->add_route( '/session-grid', [
			'methods'             => self::READABLE,
			'callback'            => function() {
				return new WP_REST_Response( [
					'html' => $this->get_html(),
				], 200 );
			},
			'permission_callback' => [ $this, 'has_public_access' ],
		] );
	}

	protected function get_html(): string
	{
		ob_start();
		Plugin::load_template('event/session-grid-body');
		return ob_get_clean();
	}
}

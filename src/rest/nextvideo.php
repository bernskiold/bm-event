<?php

namespace BernskioldMedia\WP\Event\Rest;

use BernskioldMedia\WP\Event\Queries;
use BernskioldMedia\WP\Event\SessionHelper;
use BernskioldMedia\WP\Event\Abstracts\REST_Endpoint;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

class NextVideo extends REST_Endpoint {

	protected $namespace = 'bm-events';

	protected function setup_routes(): void {
		$this->add_route( '/next-video', [
			'methods'             => self::READABLE,
			'callback'            => function() {
				$post = Queries::get_next_session();

				if ( ! $post ) {
					$data = [
						'id'             => null,
						'start_time'     => null,
						'end_time'       => null,
						'should_refresh' => false,
					];
				} else {
					$data = [
						'id'             => $post->ID,
						'start_time'     => SessionHelper::get_start_time( $post->ID ),
						'end_time'       => SessionHelper::get_end_time( $post->ID ),
						'should_refresh' => SessionHelper::should_load_next(),
					];
				}

				return new WP_REST_Response( $data, 200 );
			},
			'permission_callback' => [ $this, 'has_public_access' ],
		] );
	}
}

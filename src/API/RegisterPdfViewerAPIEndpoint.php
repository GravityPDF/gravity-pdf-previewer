<?php

namespace GFPDF\Plugins\Previewer\API;

use GFPDF\Helper\Helper_Interface_Actions;
use WP_REST_Server;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RegisterPdfViewerAPIEndpoint
 *
 * @package GFPDF\Plugins\Previewer\API
 */
class RegisterPdfViewerAPIEndpoint implements Helper_Interface_Actions {

	/**
	 * @var CallableApiResponse
	 *
	 * @since 0.1
	 */
	protected $response;

	/**
	 * RegisterPdfViewerAPIEndpoint constructor.
	 *
	 * @param CallableApiResponse $response
	 *
	 * @since 0.1
	 */
	public function __construct( CallableApiResponse $response ) {
		$this->response = $response;
	}

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$this->add_actions();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'rest_api_init', [ $this, 'register_endpoint' ] );
	}

	/**
	 * Register our PDF Streaming endpoint
	 *
	 * @Internal Use this endpoint instead of giving users a direct link to the PDF document
	 *
	 * @since    0.1
	 */
	public function register_endpoint() {
		register_rest_route(
			'gravity-pdf-previewer/v1',
			'/pdf/(?P<temp_id>[a-zA-Z0-9]+)',
			[
				'methods'  	      => WP_REST_Server::READABLE,
				'callback' 	      => [ $this->response, 'response' ],
				'permission_callback' => '__return_true',
			] 
		);
	}
}

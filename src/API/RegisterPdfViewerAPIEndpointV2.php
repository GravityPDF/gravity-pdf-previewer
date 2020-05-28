<?php

namespace GFPDF\Plugins\Previewer\API;

use GFPDF\Helper\Helper_Interface_Actions;

use WP_REST_Server;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RegisterPdfViewerAPIEndpointV2
 *
 * @package GFPDF\Plugins\Previewer\API
 */
class RegisterPdfViewerAPIEndpointV2 implements Helper_Interface_Actions {

	/**
	 * @var CallableApiResponse
	 *
	 * @since 2.0
	 */
	protected $response;

	/**
	 * RegisterPdfViewerAPIEndpoint constructor.
	 *
	 * @param CallableApiResponse $response
	 *
	 * @since 2.0
	 */
	public function __construct( CallableApiResponse $response ) {
		$this->response = $response;
	}

	/**
	 * Initialise our module
	 *
	 * @since 2.0
	 */
	public function init() {
		$this->add_actions();
	}

	/**
	 * @since 2.0
	 */
	public function add_actions() {
		add_action( 'rest_api_init', [ $this, 'register_endpoint' ] );
	}

	/**
	 * Register our PDF Streaming endpoint
	 *
	 * @Internal Use this endpoint instead of giving users a direct link to the PDF document
	 *
	 * @since    2.0
	 */
	public function register_endpoint() {
		register_rest_route(
			'gravity-pdf-previewer/v2',
			'/pdf/',
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this->response, 'response' ],
			]
		);
	}
}

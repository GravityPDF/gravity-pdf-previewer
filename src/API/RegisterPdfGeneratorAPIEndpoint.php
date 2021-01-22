<?php

namespace GFPDF\Plugins\Previewer\API;

use GFPDF\Helper\Helper_Interface_Actions;
use WP_REST_Server;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RegisterPdfGeneratorAPIEndpoint
 *
 * @package GFPDF\Plugins\Previewer\API
 */
class RegisterPdfGeneratorAPIEndpoint implements Helper_Interface_Actions {

	/**
	 * @var CallableApiResponse
	 *
	 * @since 0.1
	 */
	protected $response;

	/**
	 * RegisterPdfGeneratorAPIEndpoint constructor.
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
	 * Register our PDF generator endpoint.
	 *
	 * @Internal The Field ID is optional and needed when you want Watermark support (or any future settings we add to the PDF Preview field)
	 *
	 * @since    0.1
	 */
	public function register_endpoint() {
		register_rest_route( 'gravity-pdf-previewer/v1', '/generator/(?P<pid>[a-zA-Z0-9]+)', [
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => [ $this->response, 'response' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( 'gravity-pdf-previewer/v1', '/generator/(?P<pid>[a-zA-Z0-9]+)/(?P<fid>\d+)', [
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => [ $this->response, 'response' ],
			'permission_callback' => '__return_true',
		] );
	}
}

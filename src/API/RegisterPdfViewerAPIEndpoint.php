<?php

namespace GFPDF\Plugins\Previewer\API;

use GFPDF\Helper\Helper_Interface_Actions;

use WP_REST_Server;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2017, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF Previewer.

    Copyright (C) 2017, Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published
    by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

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
	 * @since 0.1
	 */
	public function register_endpoint() {
		register_rest_route( 'gravity-pdf-previewer/v1', '/pdf/(?P<temp_id>[a-zA-Z0-9]+)', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [ $this->response, 'response' ],
		] );
	}
}

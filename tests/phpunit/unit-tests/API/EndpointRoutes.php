<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Plugins\Previewer\API\CallableApiResponse;
use GFPDF\Plugins\Previewer\API\RegisterPdfGeneratorAPIEndpoint;
use GFPDF\Plugins\Previewer\API\RegisterPdfViewerAPIEndpoint;
use WP_UnitTestCase;
use WP_REST_Request;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF Previewer.

    Copyright (C) 2018, Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Class TestRegisterPdfGeneratorApiEndpoint
 *
 * @package GFPDF\Tests\Previewer
 *
 * @group   API
 */
class TestEndpointRoutes extends WP_UnitTestCase {

	/**
	 * Setup the REST API and endpoints
	 *
	 * @since 0.1
	 */
	public function setUp() {
		$wp_rest_server = rest_get_server();

		$api1 = new RegisterPdfGeneratorAPIEndpoint( new callableResponse() );
		$api1->init();

		$api2 = new RegisterPdfViewerAPIEndpoint( new callableResponse() );
		$api2->init();

		parent::setUp();
	}

	/**
	 * Test our endpoints are registered correctly
	 *
	 * @since 0.1
	 */
	public function test_endpoints() {

		$wp_rest_server = rest_get_server();
		do_action( 'rest_api_init' );

		$this->assertContains( 'gravity-pdf-previewer/v1', $wp_rest_server->get_namespaces() );

		$routes = $wp_rest_server->get_routes();
		$this->assertArrayHasKey( '/gravity-pdf-previewer/v1/generator/(?P<pid>[a-zA-Z0-9]+)', $routes );
		$this->assertArrayHasKey( '/gravity-pdf-previewer/v1/generator/(?P<pid>[a-zA-Z0-9]+)/(?P<fid>\d+)', $routes );
		$this->assertArrayHasKey( '/gravity-pdf-previewer/v1/pdf/(?P<temp_id>[a-zA-Z0-9]+)', $routes );
	}

}

class callableResponse implements CallableApiResponse {
	public function response( WP_REST_Request $request ) {
	}
}

<?php

namespace GFPDF\Plugins\Previewer\API;

use WP_REST_Request;

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
 * Interface CallableApiResponse
 *
 * For use in a class that handles the REST API callback which takes the WP_REST_Request class as a single argument
 *
 * @package GFPDF\Plugins\Previewer\API
 *
 * @since 0.1
 */
interface CallableApiResponse {

	/**
	 * The REST API callback
	 *
	 * @param $request
	 *
	 * @return mixed
	 *
	 * @since 0.1
	 */
	public function response( WP_REST_Request $request );
}

<?php

namespace GFPDF\Plugins\Previewer\API;

use GFPDF\Helper\Helper_Data;
use GFPDF\Model\Model_PDF;
use GFPDF\Plugins\Previewer\Exceptions\FormNotFound;
use GFPDF\Plugins\Previewer\Exceptions\PDFConfigNotFound;

use WP_REST_Request;
use GFFormsModel;
use GFAPI;
use GPDFAPI;

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
 * Class PDFViewerApiResponse
 *
 * @package GFPDF\Plugins\Previewer\API
 */
class PDFViewerApiResponse implements CallableApiResponse {

	/**
	 * @var string
	 *
	 * @since 0.1
	 */
	protected $pdf_path;

	/**
	 * PDFViewerApiResponse constructor.
	 *
	 * @param string $pdf_path
	 *
	 * @since 0.1
	 */
	public function __construct( $pdf_path ) {
		$this->pdf_path = $pdf_path;
	}

	/**
	 * Locate the PDF on the server and stream it to the client
	 *
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 *
	 * @since 0.1
	 */
	public function response( WP_REST_Request $request ) {
		$temp_id  = $request->get_param( 'temp_id' );
		$temp_pdf = $this->pdf_path . $temp_id . '/' . $temp_id . '.pdf';

		/* No file found. Trigger error */
		if ( ! is_file( $temp_pdf ) ) {
			return rest_ensure_response( [ 'error' => 'Requested PDF could not be found' ] );
		}

		$this->stream_pdf( $temp_pdf );
		$this->delete_pdf( $temp_pdf );

		exit;
	}

	/**
	 * Send out PDF to the client
	 *
	 * @param string $file Path to PDF file
	 *
	 * @since 0.1
	 */
	protected function stream_pdf( $file ) {
		/* Stream PDF */
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename="' . basename( $file ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );
		readfile( $file );
	}

	/**
	 * Remove file after it has been streamed
	 *
	 * @param string $file Path to PDF file
	 *
	 * @since 0.1
	 */
	protected function delete_pdf( $file ) {
		unlink( $file );
		rmdir( dirname( $file ) );
	}
}

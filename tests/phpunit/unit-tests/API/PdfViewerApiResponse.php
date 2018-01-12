<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Plugins\Previewer\API\PdfViewerApiResponse;

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
 * Class TestPDFViewerApiResponse
 *
 * @package GFPDF\Tests\Previewer
 *
 * @group   API
 */
class TestPDFViewerApiResponse extends WP_UnitTestCase {

	/**
	 * @var PdfViewerApiResponse
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @since 0.1
	 */
	public function setUp() {

		/* Stub our 'stream_pdf' and 'end' methods */
		$this->class = $this->getMockBuilder( '\GFPDF\Plugins\Previewer\API\PdfViewerApiResponse' )
		                    ->setConstructorArgs( [ dirname( GFPDF_PDF_PREVIEWER_FILE ) . '/tmp/' ] )
		                    ->setMethods( [ 'stream_pdf', 'end' ] )
		                    ->getMock();

		$this->class->set_logger( \GPDFAPI::get_log_class() );
	}

	/**
	 * @since 1.0
	 */
	public function test_response() {
		$pdf     = dirname( GFPDF_PDF_PREVIEWER_FILE ) . '/tmp/12345/12345.pdf';
		$request = new WP_REST_Request( 'GET' );

		/* Test PDF not found */
		$response = $this->class->response( $request );
		$this->assertEquals( 'Requested PDF could not be found', $response->data['error'] );

		/* Create a PDF and test it gets correctly cleaned up */
		$request->set_param( 'temp_id', '12345' );
		@mkdir( dirname( $pdf ) );
		@touch( $pdf, time(), mktime( null, 0, 0 ) );
		$this->class->response( $request );

		$this->assertFileNotExists( $pdf );

		/* Create a PDF and test it isn't removed initially when the download option is enabled */
		$request->set_param( 'download', '1' );
		@mkdir( dirname( $pdf ) );
		@touch( $pdf, time(), mktime( null, 0, 0 ) );

		clearstatcache();

		$this->class->response( $request );
		$this->assertFileExists( $pdf );

		clearstatcache();

		$this->class->response( $request );
		$this->assertFileNotExists( $pdf );
	}
}

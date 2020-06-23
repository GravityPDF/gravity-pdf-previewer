<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Plugins\Previewer\API\PdfViewerApiResponseV1;

use WP_UnitTestCase;
use WP_REST_Request;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TestPDFViewerApiResponse
 *
 * @package GFPDF\Tests\Previewer
 *
 * @group   API
 */
class TestPDFViewerApiResponse extends WP_UnitTestCase {

	/**
	 * @var PdfViewerApiResponseV1
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @since 0.1
	 */
	public function setUp() {

		/* Stub our 'stream_pdf' and 'end' methods */
		$this->class = $this->getMockBuilder( '\GFPDF\Plugins\Previewer\API\PdfViewerApiResponseV1' )
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
		$request->set_header( 'content-type', 'application/json' );

		/* Test PDF not found */
		$response = $this->class->response( $request );
		$this->assertEquals( 'Requested PDF could not be found', $response->data['error'] );

		/* Create a PDF and test it gets correctly cleaned up */
		$request->set_param( 'temp_id', '12345' );
		@mkdir( dirname( $pdf ) ); //phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
		@touch( $pdf, time(), mktime( null, 0, 0 ) ); //phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
		$this->class->response( $request );

		$this->assertFileNotExists( $pdf );

		/* Create a PDF and test it isn't removed initially when the download option is enabled */
		$request->set_param( 'download', '1' );
		@mkdir( dirname( $pdf ) ); //phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
		@touch( $pdf, time(), mktime( null, 0, 0 ) ); //phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged

		clearstatcache();

		$this->class->response( $request );
		$this->assertFileExists( $pdf );

		clearstatcache();

		$this->class->response( $request );
		$this->assertFileNotExists( $pdf );
	}
}

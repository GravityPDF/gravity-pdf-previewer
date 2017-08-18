<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Helper\Helper_PDF;
use GFPDF\Plugins\Previewer\API\PdfGeneratorApiResponse;

use WP_UnitTestCase;
use WP_REST_Request;
use GFAPI;
use stdClass;

/**
 * @package     Gravity PDF Core Booster
 * @copyright   Copyright (c) 2017, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF Core Booster.

    Copyright (C) 2017, Blue Liquid Designs

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
 * Class TestPDFGeneratorApiResponse
 *
 * @package GFPDF\Tests\Previewer
 *
 * @group   API
 */
class TestPDFGeneratorApiResponse extends WP_UnitTestCase {

	/**
	 * @var PdfGeneratorApiResponse
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @since 0.1
	 */
	public function setUp() {
		$this->class = new PdfGeneratorApiResponse(
			\GPDFAPI::get_mvc_class( 'Model_PDF' ),
			dirname( GFPDF_PDF_PREVIEWER_FILE ) . '/tmp/'
		);

		$this->class->set_logger( \GPDFAPI::get_log_class() );
	}

	/**
	 * @since 1.0
	 */
	public function test_response() {
		/* Setup test */
		$_SERVER['HTTP_USER_AGENT'] = 'cli';
		$form                       = json_decode( trim( file_get_contents( dirname( __FILE__ ) . '/../../json/all-form-fields.json' ) ), true );
		$form_id                    = GFAPI::add_form( $form );
		$request                    = new WP_REST_Request( 'POST', '/' );

		/* Test for missing Gravity Form error */
		$response = $this->class->response( $request );
		$this->assertEquals( 'Could not find Gravity Form', $response->data['error'] );

		$request->set_body_params( [
			'gform_submit' => $form_id,
		] );

		/* Test for missing PDF error */
		$response = $this->class->response( $request );
		$this->assertEquals( 'Could not find PDF Configuration', $response->data['error'] );

		/* Test for inactive PDF */
		$request->set_param( 'pid', '556690c8d7f82' );
		$response = $this->class->response( $request );
		$this->assertEquals( 'PDF Configuration not active #556690c8d7f82', $response->data['error'] );

		/* Test for generic error */
		$request->set_param( 'pid', 'fawf90c678523b' );
		$request->set_param( 'fid', '82' );
		$response = $this->class->response( $request );

		$this->assertEquals( 'The PDF could not be saved.', $response->data['error'] );

		/* Test PDF actually generates */
		$request->set_param( 'pid', '555ad84787d7e' );
		$response = $this->class->response( $request );

		print_r($response->data);

		$this->assertArrayHasKey( 'id', $response->data );

		/* Cleanup */
		GFAPI::delete_form( $form_id );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_pdf_config() {
		$_SERVER['HTTP_USER_AGENT'] = 'cli';
		$form                       = json_decode( trim( file_get_contents( dirname( __FILE__ ) . '/../../json/all-form-fields.json' ) ), true );
		$form_id                    = GFAPI::add_form( $form );
		$form                       = GFAPI::get_form( $form_id );

		$settings = $this->class->get_pdf_config( $form, '555ad84787d7e', 82 );

		$this->assertTrue( $settings['enable_watermark'] );
		$this->assertEquals( 'SAMPLE', $settings['watermark_text'] );
		$this->assertEquals( 'dejavusanscondensed', $settings['watermark_font'] );
		$this->assertEmpty( $settings['privileges'] );

		/* Cleanup */
		GFAPI::delete_form( $form_id );
	}

	/**
	 * @since 0.1
	 */
	public function test_unique_id() {
		$this->assertEmpty( $this->class->get_unique_id() );
		$this->class->set_unique_id();
		$this->assertNotEmpty( $this->class->get_unique_id() );
	}

	/**
	 * @since 0.1
	 */
	public function test_change_pdf_save_location() {
		$helper_pdf = new Helper_PDF(
			[ 'id' => 0, 'form_id' => 0 ],
			[],
			\GPDFAPI::get_form_class(),
			\GPDFAPI::get_data_class(),
			\GPDFAPI::get_misc_class(),
			\GPDFAPI::get_templates_class()
		);

		$this->class->set_unique_id();
		$unique_id  = $this->class->get_unique_id();
		$helper_pdf = $this->class->change_pdf_save_location( $helper_pdf );

		$this->assertEquals( dirname( GFPDF_PDF_PREVIEWER_FILE ) . '/tmp/' . $unique_id . '/' . $unique_id . '.pdf', $helper_pdf->get_full_pdf_path() );
	}

	/**
	 * @since 0.1
	 */
	public function test_add_watermark() {
		$test = new MpdfTest();
		$test = $this->class->add_watermark( $test, '', '', [] );

		$test->SetWatermarkText = function( $item ) {
			return;
		};

		$this->assertObjectNotHasAttribute( 'showWatermarkText', $test );
		$this->assertObjectNotHasAttribute( 'watermark_font', $test );

		$test = $this->class->add_watermark( $test, '', '', [
			'enable_watermark' => true,
			'watermark_font'   => '',
			'watermark_text'   => '',
		] );

		$this->assertObjectHasAttribute( 'showWatermarkText', $test );
		$this->assertObjectHasAttribute( 'watermark_font', $test );
	}

}

class MpdfTest extends stdClass {
	public function __call( $closure, $args ) {
		return call_user_func_array( $this->{$closure}, $args );
	}
}
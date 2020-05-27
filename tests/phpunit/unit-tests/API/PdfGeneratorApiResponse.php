<?php

namespace GFPDF\Tests\Previewer;

use GFAPI;
use GFPDF\Helper\Helper_PDF;
use GFPDF\Plugins\Previewer\API\PdfGeneratorApiResponse;
use GFPDF\Plugins\Previewer\Exceptions\FieldNotFound;
use stdClass;
use WP_REST_Request;
use WP_UnitTestCase;

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
		global $gfpdf;

		$this->class = new PdfGeneratorApiResponse(
			\GPDFAPI::get_mvc_class( 'Model_PDF' ),
			dirname( GFPDF_PDF_PREVIEWER_FILE ) . '/tmp/'
		);

		$this->class->set_logger( \GPDFAPI::get_log_class() );

		$fonts = glob( dirname( __FILE__ ) . '/../../fonts/' . '*.[tT][tT][fF]' );
		$fonts = ( is_array( $fonts ) ) ? $fonts : [];

		foreach ( $fonts as $font ) {
			$font_name = basename( $font );
			@copy( $font, $gfpdf->data->template_font_location . $font_name ); //phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
		}
	}

	public function tearDown() {
		global $gfpdf;

		$fonts = glob( $gfpdf->data->template_font_location . '*.[tT][tT][fF]' );
		$fonts = ( is_array( $fonts ) ) ? $fonts : [];

		foreach ( $fonts as $font ) {
			@unlink( $font ); //phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
		}

		parent::tearDown();
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

		$request->set_header( 'content-type', 'application/json' );

		/* Test for missing Gravity Form error */
		$response = $this->class->response( $request );
		$this->assertEquals( 'Could not find Gravity Form', $response->data['error'] );

		$request->set_body_params(
			[
				'gform_submit' => $form_id,
			]
		);

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
		$this->assertArrayHasKey( 'id', $response->data );

		/* Cleanup */
		GFAPI::delete_form( $form_id );
	}

	/**
	 * @since 1.1
	 */
	public function test_generate_pdf() {
		$_SERVER['HTTP_USER_AGENT'] = 'cli';
		$form_object                = json_decode( trim( file_get_contents( dirname( __FILE__ ) . '/../../json/all-form-fields.json' ) ), true );

		$form_id = GFAPI::add_form( $form_object );
		$form    = GFAPI::get_form( $form_id );

		$entry    = $this->class->create_entry( $form );
		$settings = $this->class->get_pdf_config( $form, '555ad84787d7e' );

		$reflection = new \ReflectionClass( $this->class );
		$property   = $reflection->getProperty( 'form' );
		$property->setAccessible( true );
		$property->setValue( $this->class, $form );

		$this->class->set_unique_id();
		$pdf_path = $this->class->generate_pdf( $entry, $settings );

		$this->assertSame(
			$this->class->change_legacy_pdf_save_location( '' ) . $this->class->get_unique_id() . '.pdf',
			$pdf_path
		);

		/* Cleanup */
		GFAPI::delete_form( $form_id );
	}

	/**
	 * @since 1.1
	 */
	public function test_get_pdf_preview_field() {
		$_SERVER['HTTP_USER_AGENT'] = 'cli';
		$form_object                = json_decode( trim( file_get_contents( dirname( __FILE__ ) . '/../../json/all-form-fields.json' ) ), true );

		$form_id = GFAPI::add_form( $form_object );
		$form    = GFAPI::get_form( $form_id );

		/* Run a failed test */
		try {
			$this->class->get_pdf_preview_field( $form, 1 );
		} catch ( FieldNotFound $e ) {

		}

		$this->assertEquals( 'PDF Preview field "1" not found in form "' . $form_id . '"', $e->getMessage() );

		/* Run a successful test */
		$field = $this->class->get_pdf_preview_field( $form, 73 );

		$this->assertEquals( 'PDF Preview', $field->label );

		/* Cleanup */
		GFAPI::delete_form( $form_id );
	}

	/**
	 * @since 1.1
	 */
	public function test_create_entry() {
		$_SERVER['HTTP_USER_AGENT'] = 'cli';
		$form_object                = json_decode( trim( file_get_contents( dirname( __FILE__ ) . '/../../json/post-data-form.json' ) ), true );

		$form_id = GFAPI::add_form( $form_object );
		$form    = GFAPI::get_form( $form_id );

		$_POST = json_decode( trim( file_get_contents( dirname( __FILE__ ) . '/../../json/post-data.json' ) ), true );
		add_filter( 'gfpdf_previewer_skip_file_exists_check', '__return_true' );
		$entry = $this->class->create_entry( $form );
		remove_filter( 'gfpdf_previewer_skip_file_exists_check', '__return_true' );

		/* Check the results */
		$this->assertEquals( 'First', $entry['9.3'] );
		$this->assertEquals( 'Last', $entry['9.6'] );
		$this->assertEquals( 'test@test.com', $entry['10'] );
		$this->assertEquals( '123 Fake St', $entry['11.1'] );
		$this->assertEquals( 'Line 2', $entry['11.2'] );
		$this->assertEquals( 'City', $entry['11.3'] );
		$this->assertEquals( 'State', $entry['11.4'] );
		$this->assertEquals( '2441', $entry['11.5'] );

		$this->assertNotFalse( strpos( $entry['5'], '/tmp/cefac404_input_5.jpg' ) );
		$this->assertNotFalse( strpos( $entry['6'], '/tmp/cefac404_input_6.jpg' ) );

		$files = json_decode( $entry[2], true );
		$this->assertNotFalse( strpos( $files[0], '/tmp/cefac404_input_2_o_1c39narem17i3d0g1mbc1vt1ol8k.jpg' ) );
		$this->assertNotFalse( strpos( $files[1], '/tmp/cefac404_input_2_o_1c39narem1sst199kfkvlsg111jj.jpg' ) );

		$files = json_decode( $entry[7], true );
		$this->assertNotFalse( strpos( $files[0], '/tmp/cefac404_input_7_o_1c39nb0k2dim1b481umii4c1ngj10.jpg' ) );
		$this->assertNotFalse( strpos( $files[1], '/tmp/cefac404_input_7_o_1c39nb0k21ki3l7jhvrcs5jqnv.jpg' ) );
		$this->assertNotFalse( strpos( $files[2], '/tmp/cefac404_input_7_o_1c39nb0k2tuj8m1p2s1b2gmksu.jpg' ) );

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

		/* Test with download option disabled */
		$settings = $this->class->get_pdf_config( $form, '555ad84787d7e', 82 );

		$this->assertTrue( $settings['enable_watermark'] );
		$this->assertEquals( 'SAMPLE', $settings['watermark_text'] );
		$this->assertEquals( 'dejavusanscondensed', $settings['watermark_font'] );
		$this->assertEmpty( $settings['privileges'] );
		$this->assertEquals( 'Yes', $settings['security'] );

		/* Test with download option enabled */
		$enableDownload = function( $field ) {
			$field['pdfdownload'] = true;

			return $field;
		};

		add_filter( 'gfpdf_previewer_field', $enableDownload );

		$settings = $this->class->get_pdf_config( $form, '555ad84787d7e', 82 );
		$this->assertEquals( 'No', $settings['security'] );

		/* Cleanup */
		remove_filter( 'gfpdf_previewer_field', $enableDownload );
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
			[
				'id'      => 0,
				'form_id' => 0,
			],
			[],
			\GPDFAPI::get_form_class(),
			\GPDFAPI::get_data_class(),
			\GPDFAPI::get_misc_class(),
			\GPDFAPI::get_templates_class(),
			\GPDFAPI::get_log_class()
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

		$test = $this->class->add_watermark(
			$test,
			'',
			'',
			[
				'enable_watermark' => true,
				'watermark_font'   => '',
				'watermark_text'   => '',
			]
		);

		$this->assertObjectHasAttribute( 'showWatermarkText', $test );
		$this->assertObjectHasAttribute( 'watermark_font', $test );
	}

}

class MpdfTest extends stdClass {
	public function __call( $closure, $args ) {
		return call_user_func_array( $this->{$closure}, $args );
	}
}

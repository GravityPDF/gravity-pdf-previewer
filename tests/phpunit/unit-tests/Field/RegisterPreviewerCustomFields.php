<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Helper\Helper_QueryPath;
use GFPDF\Plugins\Previewer\Field\RegisterPreviewerCustomFields;

use WP_UnitTestCase;
use GFAPI;
use GFCommon;

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
 * Class TestRegisterPreviewerCustomFields
 *
 * @package GFPDF\Tests\Previewer
 *
 * @group   field
 */
class TestRegisterPreviewerCustomFields extends WP_UnitTestCase {

	/**
	 * @var RegisterPreviewerCustomFields
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @since 0.1
	 */
	public function setUp() {
		require_once( GFCommon::get_base_path() . '/tooltips.php' );

		$this->class = new RegisterPreviewerCustomFields();
		$this->class->set_logger( \GPDFAPI::get_log_class() );
		$this->class->init();
	}

	/**
	 * @since 0.1
	 */
	public function test_tooltips() {
		global $__gf_tooltips;
		$__gf_tooltips = apply_filters( 'gform_tooltips', $__gf_tooltips );

		$this->assertArrayHasKey( 'pdf_selector_setting', $__gf_tooltips );
		$this->assertArrayHasKey( 'pdf_preview_height', $__gf_tooltips );
		$this->assertArrayHasKey( 'pdf_watermark_setting', $__gf_tooltips );
	}

	/**
	 * @since 0.1
	 */
	public function test_add_pdf_selector() {
		$_SERVER['HTTP_USER_AGENT'] = 'cli';
		$form                       = json_decode( trim( file_get_contents( dirname( __FILE__ ) . '/../../json/all-form-fields.json' ) ), true );
		$form_id                    = GFAPI::add_form( $form );

		ob_start();
		$this->class->add_pdf_selector( 25, $form_id );
		$html = ob_get_clean();

		$qp     = new Helper_QueryPath();
		$markup = $qp->html5( $html );

		$this->assertEquals( 3, $markup->find( '#pdf_selector option' )->length );
		$this->assertEquals( '555ad84787d7e', $markup->find( '#pdf_selector option:nth-child(1)' )->val() );

		/* Cleanup */
		GFAPI::delete_form( $form_id );
	}

	/**
	 * @since 0.1
	 */
	public function test_add_pdf_preview_height() {
		ob_start();
		$this->class->add_pdf_preview_height( 25 );
		$html = ob_get_clean();

		$qp     = new Helper_QueryPath();
		$markup = $qp->html5( $html );

		$this->assertEquals( 'input', $markup->find( '#pdf_preview_height' )->tag() );
	}

	/**
	 * @since 0.1
	 */
	public function test_pdf_watermark_support() {
		ob_start();
		$this->class->add_pdf_watermark_support( 25 );
		$html = ob_get_clean();

		$qp     = new Helper_QueryPath();
		$markup = $qp->html5( $html );

		$this->assertEquals( 'checkbox', $markup->find( '#pdf-watermark-setting' )->attr( 'type' ) );
		$this->assertEquals( 'input', $markup->find( '#pdf_watermark_text' )->tag() );
		$this->assertEquals( 'select', $markup->find( '#pdf_watermark_font' )->tag() );
	}

	/**
	 * @since 1.1
	 */
	public function test_download_support() {
		ob_start();
		$this->class->add_pdf_download_support( 25 );
		$html = ob_get_clean();

		$qp     = new Helper_QueryPath();
		$markup = $qp->html5( $html );

		$this->assertEquals( 'checkbox', $markup->find( '#pdf-download-setting' )->attr( 'type' ) );
	}
}
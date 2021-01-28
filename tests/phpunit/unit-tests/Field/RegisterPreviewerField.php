<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Plugins\Previewer\Field\RegisterPreviewerField;
use GFPDF\Plugins\Previewer\Field\GFormFieldPreviewer;

use GF_Fields;
use WP_UnitTestCase;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TestRegisterPreviewerField
 *
 * @package GFPDF\Tests\Previewer
 *
 * @group   field
 */
class TestRegisterPreviewerField extends WP_UnitTestCase {

	/**
	 * @var RegisterPreviewerField
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @since 0.1
	 */
	public function setUp() {
		$this->class = new RegisterPreviewerField();
		$this->class->set_logger( \GPDFAPI::get_log_class() );
	}

	/**
	 * @since 0.1
	 */
	public function test_init() {
		$this->class->init();

		$this->assertTrue( GF_Fields::exists( 'pdfpreview' ) );
		$this->assertEquals( 10, has_action( 'gform_enqueue_scripts', [ $this->class, 'gravityform_scripts' ] ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_gravityform_scripts() {
		$form = [ 'fields' => [] ];

		$this->class->gravityform_scripts( $form );
		$this->assertFalse( wp_script_is( 'gfpdf_previewer' ) );

		$form['fields'][] = new GFormFieldPreviewer();
		$this->class->gravityform_scripts( $form );
		$this->assertTrue( wp_script_is( 'gfpdf_previewer' ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_has_previewer_field() {
		$form = [ 'fields' => [] ];

		$this->assertFalse( $this->class->has_previewer_field( $form ) );

		$form['fields'][] = new GFormFieldPreviewer();

		$this->assertTrue( $this->class->has_previewer_field( $form ) );
	}
}

<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Plugins\Previewer\Field\RegisterPreviewerField;
use GFPDF\Plugins\Previewer\Field\GFormFieldPreviewer;

use GF_Fields;
use WP_UnitTestCase;

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

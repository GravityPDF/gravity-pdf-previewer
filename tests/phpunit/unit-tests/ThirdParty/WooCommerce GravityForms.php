<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Plugins\Previewer\ThirdParty\WooCommerceGravityForms;
use WP_UnitTestCase;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
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
 * Class TestWooCommerceGravityForms
 *
 * @package GFPDF\Tests\Previewer
 *
 * @group   thirdparty
 */
class TestWooCommerceGravityForms extends WP_UnitTestCase {

	/**
	 * @var \GFPDF\Plugins\Previewer\ThirdParty\WooCommerceGravityForms
	 *
	 * @since 1.1
	 */
	protected $class;

	/**
	 * @since 1.1
	 */
	public function setUp() {
		$this->class = new WooCommerceGravityForms();
	}

	/**
	 * @since 1.1
	 */
	public function test_set_form_id() {
		$this->assertSame( 5, $this->class->set_form_id( 5, [] ) );
		$this->assertSame( 8, $this->class->set_form_id( 5, [ 'gform_form_id' => 8, 'product_id' => 60 ] ) );
	}
}
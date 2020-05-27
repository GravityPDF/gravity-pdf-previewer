<?php

namespace GFPDF\Tests\Previewer;

use GFPDF\Plugins\Previewer\ThirdParty\WooCommerceGravityForms;
use WP_UnitTestCase;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		$this->assertSame(
			8,
			$this->class->set_form_id(
				5,
				[
					'gform_form_id' => 8,
					'product_id'    => 60,
				]
			)
		);
	}
}

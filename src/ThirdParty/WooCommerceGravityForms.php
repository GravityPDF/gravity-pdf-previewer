<?php

namespace GFPDF\Plugins\Previewer\ThirdParty;

use GFPDF\Helper\Helper_Interface_Filters;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooCommerceGravityForms
 *
 * @package GFPDF\Plugins\Previewer\ThirdParty
 *
 * @since   1.2
 */
class WooCommerceGravityForms implements Helper_Interface_Filters {

	/**
	 * Initiaise the class if Gravity Flow is activated
	 *
	 * @since 1.2
	 */
	public function init() {
		if ( class_exists( 'WC_GFPA_Main' ) ) {
			$this->add_filters();
		}
	}

	/**
	 * @since 1.2
	 */
	public function add_filters() {
		add_filter( 'gfpdf_previewer_form_id', [ $this, 'set_form_id' ], 10, 2 );
	}

	/**
	 * Use the correct form ID when using Gravity Flow
	 *
	 * @param int   $form_id
	 * @param array $input
	 *
	 * @return int
	 *
	 * @since 1.2
	 */
	public function set_form_id( $form_id, $input ) {
		if ( isset( $input['product_id'] ) && isset( $input['gform_form_id'] ) ) {
			$form_id = (int) $input['gform_form_id'];
		}

		return $form_id;
	}
}

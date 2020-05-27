<?php

namespace GFPDF\Plugins\Previewer\ThirdParty;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2019, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.6
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NestedFormsPerk
 *
 * @package GFPDF\Plugins\Previewer\ThirdParty
 */
class NestedFormsPerk {

	/**
	 * Initiaise the class if the Nested Forms Perk is activated
	 *
	 * @since 1.2.6
	 */
	public function init() {
		if ( function_exists( '\gp_nested_forms' ) ) {
			$this->add_action();
		}
	}

	/**
	 * @since 1.2.6
	 */
	public function add_action() {
		add_action( 'gfpdf_previewer_start_pdf_generation', [ $this, 'remove_entry_validation_for_previewer' ] );
	}

	/**
	 * Prevent entry validation occuring on Previewer entries
	 *
	 * @since 1.2.6
	 */
	public function remove_entry_validation_for_previewer() {
		$nested_forms = \gp_nested_forms();
		remove_filter( 'gform_get_field_value', [ $nested_forms, 'handle_nested_form_field_value' ] );
	}
}

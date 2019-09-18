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

/*
    This file is part of Gravity PDF Previewer.

    Copyright (C) 2019, Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published
    by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

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
		add_action( 'gfpdf_previewer_start_pdf_generation', [ $this, 'remove_entry_validation_for_previewer'] );
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

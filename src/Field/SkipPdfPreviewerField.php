<?php

namespace GFPDF\Plugins\Previewer\Field;

use GFPDF\Helper\Helper_Interface_Filters;

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
 * Class SkipPdfPreviewerField
 *
 * @package GFPDF\Plugins\Previewer\Field
 */
class SkipPdfPreviewerField implements Helper_Interface_Filters {

	/**
	 * Initialise our module
	 *
	 * @since 1.1
	 */
	public function init() {
		$this->add_filters();
	}

	/**
	 * @since 1.1
	 */
	public function add_filters() {
		add_filter( 'gfpdf_field_middleware', [ $this, 'skip_previewer_field_in_pdf' ], 10, 2 );
	}

	/**
	 * Disable the PDF Previewer field from showing up Core and Universal templates
	 *
	 * @param bool     $action
	 * @param GF_Field $field
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function skip_previewer_field_in_pdf( $action, $field ) {
		if ( $action === false ) {
			if ( $field->type === 'pdfpreview' ) {
				$action = true;
			}
		}

		return $action;
	}
}

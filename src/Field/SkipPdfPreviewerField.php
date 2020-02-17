<?php

namespace GFPDF\Plugins\Previewer\Field;

use GFPDF\Helper\Helper_Interface_Filters;

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

<?php

namespace GFPDF\Plugins\Previewer\Field;

use GFPDF\Helper\Helper_Trait_Logger;
use GFPDF\Helper\Helper_Interface_Actions;

use GF_Fields;
use Exception;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
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
 * Class RegisterPreviewerField
 *
 * @package GFPDF\Plugins\Previewer\Field
 */
class RegisterPreviewerField implements Helper_Interface_Actions {

	/*
	 * Add logging support
	 *
	 * @since 0.2
	 */
	use Helper_Trait_Logger;

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		try {
			GF_Fields::register( new GFormFieldPreviewer() );
		} catch ( Exception $e ) {
			$this->get_logger()->addError( 'Could not register Previewer field with Gravity Forms', [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			] );
		}

		$this->add_actions();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gform_enqueue_scripts', [ $this, 'gravityform_scripts' ] );
	}

	/**
	 * Load our Previewer script/styles when our custom field is in the form
	 *
	 * @param array $form
	 *
	 * @since 0.1
	 */
	public function gravityform_scripts( $form ) {

		/* Only include where our preview field is detected */
		if ( $this->has_previewer_field( $form ) ) {

			$this->get_logger()->addNotice( 'Including Previewer scripts and styles' );

			/* Add our custom JS */
			wp_enqueue_script(
				'gfpdf_previewer',
				plugin_dir_url( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/js/previewer.min.js',
				[ 'jquery' ],
				0.1,
				true
			);

			wp_localize_script(
				'gfpdf_previewer',
				'PdfPreviewerConstants',
				[
					'viewerUrl'            => plugin_dir_url( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/viewer/web/viewer.php?file=',
					'documentUrl'          => rest_url( 'gravity-pdf-previewer/v1/pdf/' ),
					'pdfGeneratorEndpoint' => rest_url( 'gravity-pdf-previewer/v1/generator/' ),

					'refreshTitle'   => __( 'Refresh PDF', 'gravity-pdf-previewer' ),
					'loadingMessage' => __( 'Loading PDF Preview', 'gravity-pdf-previewer' ),
					'errorMessage'   => sprintf( __( 'There was a problem%sloading the preview.', 'gravity-pdf-previewer' ), '<br>' ),
				]
			);

			/* Add our custom CSS */
			wp_enqueue_style(
				'gfpdf_previewer',
				plugin_dir_url( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/css/previewer.min.css',
				[],
				0.1
			);
		}
	}

	/**
	 * Checks if our preview field is present in the form
	 *
	 * @param array $form
	 *
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function has_previewer_field( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field->type === 'pdfpreview' ) {
				return true;
			}
		}

		return false;
	}
}

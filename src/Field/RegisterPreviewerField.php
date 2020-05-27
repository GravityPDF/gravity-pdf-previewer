<?php

namespace GFPDF\Plugins\Previewer\Field;

use Exception;
use GF_Fields;
use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Trait_Logger;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			$this->get_logger()->error(
				'Could not register Previewer field with Gravity Forms',
				[
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
				]
			);
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
		global $wp_rewrite;

		/* Only include where our preview field is detected */
		if ( $this->has_previewer_field( $form ) ) {

			$this->get_logger()->notice( 'Including Previewer scripts and styles' );

			$version = ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? time() : GFPDF_PDF_PREVIEWER_VERSION;

			/* Add our custom JS */
			wp_enqueue_script(
				'gfpdf_previewer',
				plugin_dir_url( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/previewer.min.js',
				[ 'jquery' ],
				$version,
				true
			);

			/*
			 * Patch for WPML which can include the default language as a GET parameter
			 * See https://github.com/GravityPDF/gravity-pdf/issues/550
			 */
			$home_url   = untrailingslashit( strtok( home_url(), '?' ) );
			$viewer_url = $home_url . '/?gpdf-viewer=1&gpdf-preview-token={TOKEN}';
			if ( $wp_rewrite->using_permalinks() ) {
				$viewer_url = $home_url . '/' . $wp_rewrite->root . 'pdf-viewer/{TOKEN}/';
			}

			wp_localize_script(
				'gfpdf_previewer',
				'PdfPreviewerConstants',
				[
					'viewerUrl'            => $viewer_url,
					'pdfGeneratorEndpoint' => rest_url( 'gravity-pdf-previewer/v1/generator/' ),

					'refreshTitle'         => __( 'Refresh PDF', 'gravity-pdf-previewer' ),
					'loadingMessage'       => __( 'Loading PDF Preview', 'gravity-pdf-previewer' ),
					'errorMessage'         => sprintf( __( 'There was a problem%sloading the preview.', 'gravity-pdf-previewer' ), '<br>' ),
				]
			);

			/* Add our custom CSS */
			wp_enqueue_style(
				'gfpdf_previewer',
				plugin_dir_url( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/previewer.min.css',
				[],
				$version
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
		if ( isset( $form['fields'] ) && is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( $field->type === 'pdfpreview' ) {
					return true;
				}
			}
		}

		return false;
	}
}

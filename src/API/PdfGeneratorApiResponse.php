<?php

namespace GFPDF\Plugins\Previewer\API;

use GFPDF\Model\Model_PDF;
use GFPDF\Helper\Helper_PDF;
use GFPDF\Helper\Helper_Trait_Logger;
use GFPDF\Plugins\Previewer\Exceptions\FieldNotFound;
use GFPDF\Plugins\Previewer\Exceptions\FormNotFound;
use GFPDF\Plugins\Previewer\Exceptions\PDFConfigNotFound;
use GFPDF\Plugins\Previewer\Exceptions\PDFNotActive;

use WP_REST_Request;
use GFFormsModel;
use GFAPI;
use GPDFAPI;
use Exception;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2017, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF Previewer.

    Copyright (C) 2017, Blue Liquid Designs

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
 * Class PdfGeneratorApiResponse
 *
 * @package GFPDF\Plugins\Previewer\API
 */
class PdfGeneratorApiResponse implements CallableApiResponse {

	/*
	 * Add logging support
	 *
	 * @since 0.2
	 */
	use Helper_Trait_Logger;

	/**
	 * @var Model_PDF
	 *
	 * @since 0.1
	 */
	protected $pdf_model;

	/**
	 * @var string
	 *
	 * @since 0.1
	 */
	protected $pdf_path;

	/**
	 * The current PDF's unique ID
	 *
	 * @var string
	 *
	 * @since 0.1
	 */
	protected $unique_id;

	/**
	 * @var array
	 *
	 * @since 0.1
	 */
	protected $form;

	/**
	 * @var array
	 *
	 * @since 0.1
	 */
	protected $entry;

	/**
	 * @var array
	 *
	 * @since 0.1
	 */
	protected $settings;

	/**
	 * PdfGeneratorApiResponse constructor.
	 *
	 * @param \GFPDF\Model\Model_PDF
	 * @param string
	 *
	 * @since 0.1
	 */
	public function __construct( Model_PDF $pdf_model, $pdf_path ) {
		$this->pdf_model = $pdf_model;
		$this->pdf_path  = $pdf_path;
	}

	/**
	 * Generate our sample PDF and return the unique ID assigned to it for use with the PdfViewerApiResponse request
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 *
	 * @since 0.1
	 */
	public function response( WP_REST_Request $request ) {

		ob_start();

		/* Get the user form data sent via the body params, the form ID and the PDF ID */
		$input    = $request->get_body_params();
		$form_id  = ( isset( $input['gform_submit'] ) ) ? (int) $input['gform_submit'] : 0;
		$pdf_id   = $request->get_param( 'pid' );
		$field_id = (int) $request->get_param( 'fid' );

		$this->get_logger()->addNotice( 'Begin generating sample PDF', [
			'input'    => $input,
			'form_id'  => $form_id,
			'pdf_id'   => $pdf_id,
			'field_id' => $field_id,
		] );

		/* Assign a unique ID to this request */
		$this->set_unique_id();

		try {
			$this->form     = $this->get_form( $form_id );
			$this->entry    = $this->create_entry( $this->form );
			$this->settings = $this->get_pdf_config( $this->form, $pdf_id, $field_id );

			/* Try create our PDF and return the Unique ID we assigned to the preview if successful */
			$this->generate_pdf( $this->entry, $this->settings );

			ob_end_clean();

			return rest_ensure_response( [ 'id' => $this->get_unique_id() ] );

		} catch ( FormNotFound $e ) {
			$this->get_logger()->addError( 'Gravity Form not found', [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			] );

			ob_end_clean();

			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		} catch ( PDFConfigNotFound $e ) {
			$this->get_logger()->addError( 'PDF Configuration Not Found', [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			] );

			ob_end_clean();

			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		} catch ( PDFNotActive $e ) {
			$this->get_logger()->addError( 'PDF Configuration Not Active', [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			] );

			ob_end_clean();

			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		} catch ( Exception $e ) {
			$this->get_logger()->addError( 'Generic Error', [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			] );

			ob_end_clean();

			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		}
	}

	/**
	 * Get the randomly-generated ID for the current entry
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_unique_id() {
		return $this->unique_id;
	}

	/**
	 * Set a randomly-generated ID for the current entry
	 *
	 * @since 0.1
	 */
	public function set_unique_id() {
		$this->unique_id = uniqid();
	}

	/**
	 * Save the PDF Preview to disk
	 *
	 * @param array $entry
	 * @param array $settings
	 *
	 * @return string The path to the generated PDF file
	 *
	 * @throws Exception If problem occured while generating PDF
	 */
	protected function generate_pdf( $entry, $settings ) {
		$this->add_previewer_filters();
		$pdf = $this->pdf_model->generate_and_save_pdf( $entry, $settings );

		if ( is_wp_error( $pdf ) ) {
			throw new Exception ( $pdf->get_error_message() );
		}

		return $pdf;
	}

	/**
	 * Change the PDF save location and add watermark support to modern templates
	 * Also add backwards compatibility support for legacy templates
	 *
	 * @since 0.1
	 */
	protected function add_previewer_filters() {
		add_filter( 'gfpdf_pdf_generator_pre_processing', [ $this, 'change_pdf_save_location' ] );
		add_filter( 'gfpdf_mpdf_init_class', [ $this, 'add_watermark' ], 10, 4 );

		/* Handle Legacy Tier 2 templates */
		add_filter( 'gfpdf_legacy_save_path', [ $this, 'change_legacy_pdf_save_location' ] );
		add_filter( 'gform_form_post_get_meta_' . $this->form['id'], [ $this, 'override_form_meta' ] );
		add_filter( 'gfpdf_entry_pre_form_data', [ $this, 'override_entry' ] );
		add_filter( 'mpdf_import_use', [ $this, 'add_watermark_to_legacy_pdf' ] );
	}

	/**
	 * Change the location the PDF is saved into so the temporary documents are easier to manage,
	 * limit the potential for conflict with completed PDFs, and reduce security risks
	 *
	 * @param Helper_PDF $pdf_generator
	 *
	 * @return Helper_PDF
	 *
	 * @since 0.1
	 */
	public function change_pdf_save_location( Helper_PDF $pdf_generator ) {
		$pdf_generator->set_path( $this->pdf_path . $this->get_unique_id() . '/' );
		$pdf_generator->set_filename( $this->get_unique_id() );

		$this->get_logger()->addNotice( 'Change PDF Location / Filename', [
			'path' => $this->pdf_path,
			'name' => $this->get_unique_id(),
		] );

		return $pdf_generator;
	}

	/**
	 * Change the PDF save location for legacy PDF templates
	 *
	 * @param $path
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function change_legacy_pdf_save_location( $path ) {
		return $this->pdf_path . $this->get_unique_id() . '/';
	}

	/**
	 * Enable a Watermark on the PDF, if the user has set this
	 *
	 * @param Mpdf  $mpdf
	 * @param array $form
	 * @param array $entry
	 * @param array $settings
	 *
	 * @return Mpdf
	 *
	 * @since 0.1
	 */
	public function add_watermark( $mpdf, $form, $entry, $settings ) {
		if ( isset( $settings['enable_watermark'] ) && $settings['enable_watermark'] ) {
			$mpdf->showWatermarkText = true;
			$mpdf->watermark_font    = $settings['watermark_font'];
			$mpdf->SetWatermarkText( $settings['watermark_text'] );

			$this->get_logger()->addNotice( 'Enable PDF Watermark Support' );
		}

		return $mpdf;
	}

	/**
	 * Add watermark support to legacy Tier 2 PDF templates
	 *
	 * @param $mpdf
	 *
	 * @since 0.1
	 */
	public function add_watermark_to_legacy_pdf( $mpdf ) {
		if ( class_exists( 'gfpdfe_business_plus' ) ) {
			$this->add_watermark( $mpdf, $this->form, $this->entry, $this->settings );
		}
	}

	/**
	 * Check the PDF Preview form field for Watermark settings and assign to our PDF Settings
	 *
	 * @param array $settings
	 * @param array $form
	 * @param int   $field_id
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	protected function setup_watermark_support( $settings, $form, $field_id ) {
		try {
			$field                        = $this->get_pdf_preview_field( $form, $field_id );
			$settings['enable_watermark'] = ( isset( $field['pdfwatermarktoggle'] ) && $field['pdfwatermarktoggle'] ) ? true : false;
			$settings['watermark_text']   = $field['pdfwatermarktext'];
			$settings['watermark_font']   = $field['pdfwatermarkfont'];
		} catch ( FieldNotFound $e ) {
			/* do nothing */
			$this->get_logger()->addWarning( $e->getMessage() );
		}

		return $settings;
	}

	/**
	 * Find the current form's PDF Preview field object based on the field ID
	 *
	 * @param array $form
	 * @param int   $field_id
	 *
	 * @return GF_FIeld
	 *
	 * @throws FieldNotFound
	 *
	 * @since 0.1
	 */
	protected function get_pdf_preview_field( $form, $field_id ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field->id === $field_id && $field->get_input_type() === 'pdfpreview' ) {
				return $field;
			}
		}

		throw new FieldNotFound( sprintf( 'PDF Preview field "%s" not found in form "%s"', $field_id, $form['id'] ) );
	}

	/**
	 * Setup security so end user's cannot do anything with the documents (in Adobe Reader anyway)
	 *
	 * @param array $settings
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	protected function override_security_settings( $settings ) {
		$settings['security']        = 'Yes';
		$settings['password']        = '';
		$settings['master_password'] = '';
		$settings['privileges']      = [];
		$settings['format']          = 'Standard';

		return $settings;
	}

	/**
	 * Gets the current Gravity Form (if any), applies filters and removes display only fields and ignored fields
	 *
	 * @param int $form_id The form ID
	 *
	 * @return array
	 *
	 * @throws FormNotFound
	 *
	 * @since 0.1
	 */
	protected function get_form( $form_id ) {

		$form = GFAPI::get_form( $form_id );

		if ( ! $form ) {
			/* Throw exception */
			throw new FormNotFound( $form_id );
		}

		$form = apply_filters( 'gform_pre_render', $form, false, [] );
		$form = apply_filters( 'gform_pre_render_' . $form['id'], $form, false, [] );

		$ignore_types = [
			'creditcard',
		];

		/* Remove ignored fields and display-only fields */
		$form['fields'] = array_filter( $form['fields'], function( $field ) use ( $ignore_types ) {
			return ! ( $field->displayOnly || in_array( $field->get_input_type(), $ignore_types ) );
		} );

		$form['fields'] = array_values( $form['fields'] );

		return $form;
	}

	/**
	 * Get the PDF configuration based off the form ID and the PDF ID
	 *
	 * @param array  $form
	 * @param string $pdf_id
	 * @param int    $field_id
	 *
	 * @return array
	 *
	 * @throws PDFConfigNotFound
	 * @throws PDFNotActive
	 *
	 * @since 0.1
	 */
	public function get_pdf_config( $form, $pdf_id, $field_id = 0 ) {
		$pdf_config = GPDFAPI::get_pdf( $form['id'], $pdf_id );

		if ( is_wp_error( $pdf_config ) ) {
			throw new PDFConfigNotFound( $pdf_id );
		}

		if ( $pdf_config['active'] !== true ) {
			throw new PDFNotActive( $pdf_id );
		}

		$pdf_config = $this->override_security_settings( $pdf_config );
		$pdf_config = $this->setup_watermark_support( $pdf_config, $form, $field_id );

		return $pdf_config;
	}

	/**
	 * Create a new $entry object based off the $_POST data sent with this API request
	 *
	 * @param array $form
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	protected function create_entry( $form ) {
		do_action( 'gform_pre_submission', $form );
		do_action( 'gform_pre_submission_' . $form['id'], $form );

		$entry = GFFormsModel::create_lead( $form );
		$entry = $this->add_upload_support( $entry, $form );

		$entry['date_created'] = current_time( 'mysql', true );
		$entry['id']           = $this->get_unique_id();

		gform_delete_meta( $entry['id'] );

		return $entry;
	}

	/**
	 * Handle upload fields so they show up in the PDF (mostly) correctly
	 *
	 * @Internal The filename will be incorrect as its stored in a tmp directory
	 *
	 * @param array $entry
	 *
	 * @return array
	 *
	 * @since    0.1
	 */
	protected function add_upload_support( $entry ) {

		if ( isset( $_POST['gform_uploaded_files'] ) ) {
			$tmp_path = GFFormsModel::get_upload_path( $entry['form_id'] ) . '/tmp/';
			$tmp_url  = GFFormsModel::get_upload_url( $entry['form_id'] ) . '/tmp/';

			$field_files_array = (array) json_decode( stripslashes( $_POST['gform_uploaded_files'] ), true );

			foreach ( $field_files_array as $key => $field_files ) {
				$field_id = explode( '_', $key )[1];

				if ( is_array( $field_files ) ) {
					$files = [];
					foreach ( $field_files as $file ) {
						if ( is_file( $tmp_path . $file['temp_filename'] ) ) {
							$files[] = $tmp_url . $file['temp_filename'];
						}
					}

					$entry[ $field_id ] = json_encode( $files );
				} else {
					$single_image_tmp_name = $_POST['gform_unique_id'] . '_' . $key . '.' . pathinfo( $field_files, PATHINFO_EXTENSION );

					if ( is_file( $tmp_path . $single_image_tmp_name ) ) {
						$entry[ $field_id ] = $tmp_url . $single_image_tmp_name;
					}
				}
			}
		}

		return $entry;
	}

	/**
	 * Used to set the current preview form when getting the form
	 *
	 * @param $form
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function override_form_meta( $form ) {
		return $this->form;
	}

	/**
	 * Used to set the current preview entry when processing the $form_data array
	 *
	 * @param $entry
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function override_entry( $entry ) {
		return $this->entry;
	}
}
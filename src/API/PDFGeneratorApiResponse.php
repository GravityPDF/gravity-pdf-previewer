<?php

namespace GFPDF\Plugins\Previewer\API;

use GFPDF\Helper\Helper_Data;
use GFPDF\Model\Model_PDF;
use GFPDF\Plugins\Previewer\Exceptions\FieldNotFound;
use GFPDF\Plugins\Previewer\Exceptions\FormNotFound;
use GFPDF\Plugins\Previewer\Exceptions\PDFConfigNotFound;

use WP_REST_Request;
use GFFormsModel;
use GFAPI;
use GPDFAPI;

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
 * Class PDFGeneratorApiResponse
 *
 * @package GFPDF\Plugins\Previewer\API
 */
class PDFGeneratorApiResponse implements CallableApiResponse {

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
	 * PDFGeneratorApiResponse constructor.
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
	 * Generate our sample PDF and return the unique ID assigned to it
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 *
	 * @since 0.1
	 */
	public function response( WP_REST_Request $request ) {

		/* Get the user form data sent via the body params, the form ID and the PDF ID */
		$input    = $request->get_body_params();
		$form_id  = ( isset( $input['gform_submit'] ) ) ? (int) $input['gform_submit'] : 0;
		$pdf_id   = $request->get_param( 'pid' );
		$field_id = (int) $request->get_param( 'fid' );

		/* Assign a unique ID to this request */
		$this->unique_id = uniqid();

		try {
			$form  = $this->get_form( $form_id );
			$entry = $this->create_entry( $form );

			$settings = $this->get_pdf_config( $form_id, $pdf_id );
			$settings = $this->override_security_settings( $settings );
			$settings = $this->setup_watermark_support( $settings, $form, $field_id );

			$this->generate_pdf( $entry, $settings );

			return rest_ensure_response( [ 'id' => $this->unique_id ] ); /* Return the Unique ID for our PDF */

		} catch ( FormNotFound $e ) {
			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		} catch ( PDFConfigNotFound $e ) {
			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		} catch ( Exception $e ) {
			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		}
	}

	/**
	 * Save the PDF to disk
	 *
	 * @param array $entry
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @throw Exception
	 */
	protected function generate_pdf( $entry, $settings ) {
		add_filter( 'gfpdf_pdf_generator_pre_processing', [ $this, 'change_pdf_save_location' ] );
		add_filter( 'gfpdf_mpdf_init_class', [ $this, 'add_watermark' ], 10, 4 );

		$pdf = $this->pdf_model->generate_and_save_pdf( $entry, $settings );

		if ( is_wp_error( $pdf ) ) {
			throw new Exception ( $pdf->get_error_message() );
		}

		return $pdf;
	}

	/**
	 * Change the location the PDF is saved into so the temporary documents are easier to manage
	 * and limit the potential for conflict with completed PDFs
	 *
	 * @param Helper_PDF $pdf_generator
	 *
	 * @return Helper_PDF
	 *
	 * @since 0.1
	 */
	public function change_pdf_save_location( $pdf_generator ) {
		$pdf_generator->set_path( $this->pdf_path . $this->unique_id . '/' );
		$pdf_generator->set_filename( $this->unique_id );

		return $pdf_generator;
	}

	/**
	 * Enable a Watermark on the PDF, if the user wants it
	 *
	 * @param Mpdf $mpdf
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
		}

		return $mpdf;
	}

	protected function setup_watermark_support( $settings, $form, $field_id ) {
		try {
			$field                        = $this->get_pdf_preview_field( $form, $field_id );
			$settings['enable_watermark'] = ( isset( $field['pdfwatermarktoggle'] ) && $field['pdfwatermarktoggle'] ) ? true : false;
			$settings['watermark_text']   = $field['pdfwatermarktext'];
			$settings['watermark_font']   = $field['pdfwatermarkfont'];
		} catch ( FieldNotFound $e ) {
			/* do nothing */
		}

		return $settings;
	}

	protected function get_pdf_preview_field( $form, $field_id ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field->id === $field_id && $field->get_input_type() === 'pdfpreview' ) {
				return $field;
			}
		}

		throw new FieldNotFound( sprintf( 'PDF Preview field "%s" not found in form "%s"', $field_id, $form['id'] ) );
	}

	/**
	 * Setup security so end user's cannot do anything with the documents in Adobe Reader
	 *
	 * @param array $settings
	 *
	 * @return array
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
	 */
	protected function get_form( $form_id ) {

		$form = GFAPI::get_form( $form_id );

		if ( ! $form ) {
			/* Throw exception */
			throw new FormNotFound( $form_id );
		}

		$form = apply_filters( 'gform_pre_render', $form, false, [] );
		$form = apply_filters( 'gform_pre_render_' . $form['id'], $form, false, [] );

		/* @TODO - add support for uploads */
		$ignore_types = [
			'fileupload',
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
	 * @param int    $form_id
	 * @param string $pdf_id
	 *
	 * @return array
	 *
	 * @throws PDFConfigNotFound
	 */
	protected function get_pdf_config( $form_id, $pdf_id ) {
		$pdf_config = GPDFAPI::get_pdf( $form_id, $pdf_id );

		if ( is_wp_error( $pdf_config ) ) {
			throw new PDFConfigNotFound( $pdf_id );
		}

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
		return GFFormsModel::create_lead( $form );
	}
}

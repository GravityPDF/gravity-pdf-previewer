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
use GFCache;
use GPDFAPI;
use Exception;
use GFCommon;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		do_action( 'gfpdf_previewer_start_pdf_generation', $request );

		/* Get the user form data sent via the body params, the form ID and the PDF ID */
		$input    = $request->get_body_params();
		$pdf_id   = $request->get_param( 'pid' );
		$field_id = (int) $request->get_param( 'fid' );

		$form_id = ( isset( $input['gform_submit'] ) ) ? (int) $input['gform_submit'] : 0;
		$form_id = apply_filters( 'gfpdf_previewer_form_id', $form_id, $input, $request );

		$this->get_logger()->notice( 'Begin generating sample PDF', [
			'input'    => $input,
			'form_id'  => $form_id,
			'pdf_id'   => $pdf_id,
			'field_id' => $field_id,
		] );

		/* Assign a unique ID to this request */
		$this->set_unique_id();

		try {
			$this->setup_doing_previewer_constant();
			$this->form     = $this->get_form( $form_id );
			$this->settings = $this->get_pdf_config( $this->form, $pdf_id, $field_id );
			$this->entry    = apply_filters( 'gfpdf_previewer_created_entry', $this->create_entry( $this->form ), $this->form, $this->settings, $input, $request );

			/* Try create our PDF and return the Unique ID we assigned to the preview if successful */
			$pdf_path = $this->generate_pdf( $this->entry, $this->settings );

			/* Set the last access time to the current hour for ad-hoc security in PdfViewerApiResponse */
			touch( $pdf_path, time(), mktime( null, 0, 0 ) );

			do_action( 'gfpdf_previewer_end_pdf_generation', $request, $this->form, $this->entry, $this->settings, $input );

			ob_end_clean();

			return rest_ensure_response( [ 'id' => $this->get_unique_id() ] );

		} catch ( FormNotFound $e ) {
			$this->get_logger()->error( 'Gravity Form not found', [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			] );

			ob_end_clean();

			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		} catch ( PDFConfigNotFound $e ) {
			$this->get_logger()->error( 'PDF Configuration Not Found', [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			] );

			ob_end_clean();

			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		} catch ( PDFNotActive $e ) {
			$this->get_logger()->error( 'PDF Configuration Not Active', [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			] );

			ob_end_clean();

			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		} catch ( Exception $e ) {
			$this->get_logger()->error( 'Generic Error', [
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
	public function generate_pdf( $entry, $settings ) {
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

		$this->get_logger()->notice( 'Change PDF Location / Filename', [
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

			$this->get_logger()->notice( 'Enable PDF Watermark Support' );
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
	 * @param array $field
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	protected function setup_watermark_support( $settings, $field ) {
		$settings['enable_watermark'] = ( isset( $field['pdfwatermarktoggle'] ) && $field['pdfwatermarktoggle'] ) ? true : false;
		$settings['watermark_text']   = isset( $field['pdfwatermarktext'] ) ? $field['pdfwatermarktext'] : '';
		$settings['watermark_font']   = isset( $field['pdfwatermarkfont'] ) ? $field['pdfwatermarkfont'] : '';

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
	public function get_pdf_preview_field( $form, $field_id ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field->id === $field_id && $field->type === 'pdfpreview' ) {
				return apply_filters( 'gfpdf_previewer_field', $field, $form );
			}
		}

		throw new FieldNotFound( sprintf( 'PDF Preview field "%s" not found in form "%s"', $field_id, $form['id'] ) );
	}

	/**
	 * Setup security so end user's cannot do anything with the documents (in Adobe Reader anyway)
	 *
	 * @param array $settings
	 * @param array $field
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	protected function override_security_settings( $settings, $field ) {

		/* PDF security settings skipped if disabled via filter */
		if ( ! apply_filters( 'gfpdf_previewer_enable_pdf_security', true, $settings, $field ) ) {
			return $settings;
		}

		$allow_download = isset( $field['pdfdownload'] ) ? (bool) $field['pdfdownload'] : false;
		if ( ! $allow_download ) {
			$settings['security']        = 'Yes';
			$settings['password']        = '';
			$settings['master_password'] = '';
			$settings['privileges']      = [];
			$settings['format']          = 'Standard';
		} else {
			$settings['security']        = 'No';
		}

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
	public function get_form( $form_id ) {

		$form = GFAPI::get_form( $form_id );

		if ( ! $form ) {
			/* Throw exception */
			throw new FormNotFound( $form_id );
		}

		/* Add better compatibility for code that taps into gform_pre_render */
		require_once( \GFCommon::get_base_path() . '/form_display.php' );

		$form = apply_filters( 'gform_pre_render', $form, false, [] );
		$form = apply_filters( 'gform_pre_render_' . $form['id'], $form, false, [] );

		$ignore_types = [
			'creditcard',
		];

		/* Remove ignored fields and display-only fields */
		$form['fields'] = array_filter( $form['fields'], function( $field ) use ( $ignore_types ) {
			return ! ( in_array( $field->get_input_type(), $ignore_types ) );
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

		$field = [];
		try {
			$field = $this->get_pdf_preview_field( $form, $field_id );
		} catch ( FieldNotFound $e ) {
			/* do nothing */
			$this->get_logger()->warning( $e->getMessage() );
		}

		$pdf_config = $this->override_security_settings( $pdf_config, $field );
		$pdf_config = $this->setup_watermark_support( $pdf_config, $field );

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
	public function create_entry( $form ) {
		do_action( 'gform_pre_submission', $form );
		do_action( 'gform_pre_submission_' . $form['id'], $form );

		$entry = GFFormsModel::create_lead( $form );

		$entry = $this->fix_upload_encoding( $entry, $form );
		$entry = $this->add_upload_support( $entry, $form );

		$entry['date_created'] = current_time( 'mysql', true );
		$entry['id']           = $this->get_unique_id();

		GFCache::flush();

		$this->cache_product_data( $entry, $form );

		return $entry;
	}

	/**
	 * The file upload fields can be double json_encoded, so we'll decode the value if needed
	 *
	 * @param array $entry
	 * @param array $form
	 *
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function fix_upload_encoding( $entry, $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field->get_input_type() === 'fileupload' || $field->get_input_type() === 'post_image' ) {
				$field_key = $field->id;
				if ( isset( $entry[ $field_key ] ) && strpos( $entry[ $field_key ], '\\\\\\/' ) !== false ) {
					$entry[ $field_key ] = json_decode( $entry[ $field_key ] );
				}
			}
		}

		return $entry;
	}

	/**
	 * Handle upload fields so they show up in the PDF (mostly) correctly
	 *
	 * @Internal The filename will be incorrect as its stored in a tmp directory
	 *
	 * @param array $entry
	 * @param array $form
	 *
	 * @return array
	 *
	 * @since    0.1
	 */
	protected function add_upload_support( $entry, $form ) {

		if ( isset( $_POST['gform_uploaded_files'] ) ) {
			$existing_entry_id = apply_filters( 'gfpdf_previewer_entry_id', rgpost( 'lid' ), $form, $entry );

			$db_entry          = ( ! empty( $existing_entry_id ) ) ? GFAPI::get_entry( $existing_entry_id ) : null;
			$field_files_array = (array) json_decode( stripslashes( $_POST['gform_uploaded_files'] ), true );

			foreach ( $form['fields'] as $field ) {
				if ( $field->get_input_type() === 'fileupload' || $field->get_input_type() === 'post_image' ) {
					$method = ( ! $field->multipleFiles ) ? 'update_single_file_upload_field' : 'update_multi_file_upload_field';
					$entry  = $this->$method( $entry, $field, $field_files_array, $db_entry );
				}
			}
		}

		return $entry;
	}

	/**
	 * Updates the current entry with the correct single file upload information
	 *
	 * @param array      $entry    The Gravity Forms entry generated from $_POST data
	 * @param array      $field    The current upload field being processed
	 * @param array      $files    The newly-uploaded files
	 * @param array|null $db_entry The existing DB entry, if any
	 *
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function update_single_file_upload_field( $entry, $field, $files, $db_entry = null ) {
		list( $tmp_path, $tmp_url ) = $this->get_tmp_info( $entry['form_id'] );

		$input                 = 'input_' . $field->id;
		$single_image_tmp_name = rgpost( 'gform_unique_id' ) . '_' . $input . '.' . pathinfo( $files[ $input ], PATHINFO_EXTENSION );

		$override = apply_filters( 'gfpdf_previewer_skip_file_exists_check', false, $entry, $field, $files, $db_entry );
		if ( $override || is_file( $tmp_path . $single_image_tmp_name ) ) {
			$entry[ $field->id ] = $tmp_url . $single_image_tmp_name;
		} elseif ( ! empty( $db_entry ) && isset( $db_entry[ $field->id ] ) ) {
			$entry[ $field->id ] = $db_entry[ $field->id ];
		}

		return $entry;
	}

	/**
	 * Updates the current entry with the correct multi file upload information
	 *
	 * @param array      $entry    The Gravity Forms entry generated from $_POST data
	 * @param array      $field    The current upload field being processed
	 * @param array      $files    The newly-uploaded files
	 * @param array|null $db_entry The existing DB entry, if any
	 *
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function update_multi_file_upload_field( $entry, $field, $files, $db_entry = null ) {
		list( $tmp_path, $tmp_url ) = $this->get_tmp_info( $entry['form_id'] );

		$value = ( ! empty( $db_entry ) && isset( $db_entry[ $field->id ] ) ) ? $db_entry[ $field->id ] : '';

		$input   = 'input_' . $field->id;
		$uploads = [];
		$override = apply_filters( 'gfpdf_previewer_skip_file_exists_check', false, $entry, $field, $files, $db_entry );

		if ( isset( $files[ $input ] ) ) {
			foreach ( $files[ $input ] as $file ) {
				if ( $override || is_file( $tmp_path . $file['temp_filename'] ) ) {
					$uploads[] = $tmp_url . $file['temp_filename'];
				}
			}
		}

		/* Merge the DB upload field with the new uploaded files */
		if ( ! empty( $value ) ) {
			$value   = json_decode( $value, true );
			$uploads = array_merge( $value, $uploads );
		}

		$entry[ $field->id ] = json_encode( $uploads );

		return $entry;
	}

	/**
	 * Returns the Gravity Forms tmp upload path and URL as an array
	 *
	 * @param int $form_id
	 *
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function get_tmp_info( $form_id ) {
		return [
			GFFormsModel::get_upload_path( $form_id ) . '/tmp/',
			GFFormsModel::get_upload_url( $form_id ) . '/tmp/',
		];
	}

	/**
	 * Developers to check for this constant to change their PDF template output
	 *
	 * @since 1.1
	 */
	protected function setup_doing_previewer_constant() {
		if( ! defined( 'DOING_PDF_PREVIEWER' ) ) {
			define( 'DOING_PDF_PREVIEWER', true );
		}
	}

	/**
	 * Store the product data in a temporary cache for the request
	 *
	 * @param array $entry
	 * @param array $form
	 *
	 * @since 1.3
	 */
	protected function cache_product_data( $entry, $form ) {
		global $_gform_lead_meta;

		$entry_id = $entry['id'];
		unset( $entry['id'] );

		$products = GFCommon::get_product_fields( $form, $entry, true );

		$cache_key                      = get_current_blog_id() . '_' . $entry_id . '_gform_product_info_1_';
		$_gform_lead_meta[ $cache_key ] = maybe_serialize( $products );
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
		remove_filter( 'gfpdf_entry_pre_form_data', [ $this, 'override_entry'] );

		return $this->entry;
	}
}

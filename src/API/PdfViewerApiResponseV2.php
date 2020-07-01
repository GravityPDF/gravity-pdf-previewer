<?php

namespace GFPDF\Plugins\Previewer\API;

use GFAPI;
use GFPDF\Helper\Helper_Trait_Logger;
use GFPDF\Plugins\Previewer\Validation\Token;
use WP_REST_Request;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PdfViewerApiResponseV2
 *
 * @package GFPDF\Plugins\Previewer\API
 */
class PdfViewerApiResponseV2 implements CallableApiResponse {

	/*
	 * Add logging support
	 *
	 * @since 2.0
	 */
	use Helper_Trait_Logger;

	/**
	 * @var Token
	 *
	 * @since 2.0
	 */
	protected $token;

	/**
	 * @var string
	 *
	 * @since 2.0
	 */
	protected $pdf_path;

	/**
	 * PdfViewerApiResponse constructor.
	 *
	 * @param Token  $token
	 * @param string $pdf_path
	 *
	 * @since 2.0
	 */
	public function __construct( Token $token, $pdf_path ) {
		$this->token    = $token;
		$this->pdf_path = $pdf_path;
	}

	/**
	 * Locate the PDF on the server using a temporary ID and stream it to the client
	 *
	 * @Internal The temp ID is provided using the PdfGeneratorApiResponse endpoint
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 *
	 * @since    2.0
	 */
	public function response( WP_REST_Request $request ) {

		try {
			list( $form_id, $field_id, $tmp_id, $pdf_name ) = $this->token->validate( $this->get_token( $request ) );

			/* @TODO - download */

			$tmp_pdf        = $this->pdf_path . "$tmp_id/$tmp_id.pdf";
			$allow_download = $this->get_field_settings( $this->get_form( $form_id ), $field_id, 'pdfdownload' );
			$this->get_logger()->notice(
				'Begin streaming Preview PDF',
				[
					'id'  => $tmp_id,
					'pdf' => $tmp_pdf,
				]
			);


			$access_number = $this->get_access_limit( $tmp_pdf );
			$this->stream_pdf( $tmp_pdf, $pdf_name );
			$access_number = $this->update_access_limit( $tmp_pdf, $access_number );

			/*
			 * Some browsers will give users the cached copy of the PDF. Some will try download it from the source.
			 * When the download setting is enabled we won't delete the PDF by default. Instead, we'll rely on a crude access
			 * policy using the PDF's last accessed time, which we'll manually update.
			 *
			 * If the access policy doesn't work, the PDF will be cleaned up as part of our clean-up cron.
			 */
			if ( empty( $allow_download ) || $allow_download === false || $access_number === 2 ) {
				$this->delete_pdf( $tmp_pdf );
			}
		} catch ( Exception $e ) {
			return rest_ensure_response( [ 'error' => $e->getMessage() ] );
		}

		$this->end();
	}

	protected function get_token( $request ) {
		$token = $request->get_param( 'token' );
		$token = str_replace( ' ', '+', $token );
		$token = rawurldecode( $token );

		return $token;
	}

	/**
	 * Send out PDF to the client
	 *
	 * @param string $file Path to PDF file
	 * @param string $name The name of the PDF file
	 *
	 * @since 2.0
	 */
	protected function stream_pdf( $file, $name ) {
		/* Stream PDF */
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename*="UTF-8\'\'' . rawurlencode( $name ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: none' );
		readfile( $file );
	}

	/**
	 * Remove file /folder after it has been streamed
	 *
	 * @param string $file Path to PDF file
	 *
	 * @since 2.0
	 */
	protected function delete_pdf( $file ) {
		unlink( $file );
		rmdir( dirname( $file ) );
	}

	/**
	 * Exit the process after streaming the PDF
	 *
	 * @Interal In its own method so we can easily mock it for unit testing
	 *
	 * @since   2.0
	 */
	protected function end() {
		exit;
	}

	/**
	 * Get the current access limit
	 *
	 * @param string $path Full path to PDF
	 *
	 * @return int
	 * @internal We manually set the last access hour time to zero when we created the PDF
	 *
	 */
	protected function get_access_limit( $path ) {
		$access_limit = (int) gmdate( 'i', fileatime( $path ) );

		return ( in_array( $access_limit, [ 0, 1, 2 ], true ) ) ? $access_limit : 0;
	}

	/**
	 * Updates the access limit and stores it with the PDF file's last access time
	 *
	 * @param string $path  Full path to PDF
	 * @param int    $limit The number of times the PDF has been downloaded
	 *
	 * @return int
	 *
	 * @since 1.1
	 */
	protected function update_access_limit( $path, $limit ) {
		touch( $path, time(), mktime( null, ++$limit, 0 ) );

		return $limit;
	}

	/**
	 * Get the form information
	 *
	 * @param int $form_id Form Id
	 *
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function get_form( $form_id ) {
		return GFAPI::get_form( $form_id );

	}

	/**
	 * Get the field settings
	 *
	 * @param array  $form     Form settings.
	 *
	 * @param int    $field_id Field ID.
	 *
	 * @param string $key      Setting's key name
	 *
	 * @return mixed
	 *
	 * @since 1.1
	 */
	protected function get_field_settings( $form, $field_id, $key = "" ) {

		$field_key  = array_search( $field_id, array_column( $form['fields'], 'id' ) );
		$field_settings = $form['fields'][$field_key];

		if ( $key === "" ) {
			return $field_settings; /* Return's the whole settings array if no key was specified*/
		}

		if ( empty( $field_settings[ $key ] ) ) { /* Check if the specified key exists ,if not set return to NULL*/
			return NULL;
		} else {
			return $field_settings[ $key ]; /* Return's specified key's value. */
		}

	}

}

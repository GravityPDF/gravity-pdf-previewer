<?php

namespace GFPDF\Plugins\Previewer\API;

use GFPDF\Helper\Helper_Trait_Logger;
use WP_REST_Request;

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
 * Class PdfViewerApiResponseV1
 *
 * @package GFPDF\Plugins\Previewer\API
 */
class PdfViewerApiResponseV1 implements CallableApiResponse {

	/*
	 * Add logging support
	 *
	 * @since 0.2
	 */
	use Helper_Trait_Logger;

	/**
	 * @var string
	 *
	 * @since 0.1
	 */
	protected $pdf_path;

	/**
	 * PdfViewerApiResponse constructor.
	 *
	 * @param string $pdf_path
	 *
	 * @since 0.1
	 */
	public function __construct( $pdf_path ) {
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
	 * @since    0.1
	 */
	public function response( WP_REST_Request $request ) {

		_doing_it_wrong( __METHOD__, 'The v1 API has been deprecated. Use the v2 API.', '2.0' );
		$temp_id        = $request->get_param( 'temp_id' );
		$allow_download = $request->get_param( 'download' );
		$temp_pdf       = $this->pdf_path . $temp_id . '/' . $temp_id . '.pdf';

		$this->get_logger()->notice(
			'Begin streaming Preview PDF',
			[
				'id'  => $temp_id,
				'pdf' => $temp_pdf,
			]
		);

		/* No file found. Trigger error */
		if ( ! is_file( $temp_pdf ) ) {
			$this->get_logger()->error( 'PDF Not Found' );
			return rest_ensure_response( [ 'error' => 'Requested PDF could not be found' ] );
		}

		$access_number = $this->get_access_limit( $temp_pdf );
		$this->stream_pdf( $temp_pdf );
		$access_number = $this->update_access_limit( $temp_pdf, $access_number );

		/*
		 * Some browsers will give users the cached copy of the PDF. Some will try download it from the source.
		 * When the download setting is enabled we won't delete the PDF by default. Instead, we'll rely on a crude access
		 * policy using the PDF's last accessed time, which we'll manually update.
		 *
		 * If the access policy doesn't work, the PDF will be cleaned up as part of our clean-up cron.
		 */
		if ( empty( $allow_download ) || $access_number === 2 ) {
			$this->delete_pdf( $temp_pdf );
		}

		$this->end();
	}

	/**
	 * Send out PDF to the client
	 *
	 * @param string $file Path to PDF file
	 *
	 * @since 0.1
	 */
	protected function stream_pdf( $file ) {
		/* Stream PDF */
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename="' . basename( $file ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: none' );
		readfile( $file );
	}

	/**
	 * Remove file /folder after it has been streamed
	 *
	 * @param string $file Path to PDF file
	 *
	 * @since 0.1
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
	 * @since   0.1
	 */
	protected function end() {
		exit;
	}

	/**
	 * Get the current access limit
	 *
	 * @internal We manually set the last access hour time to zero when we created the PDF
	 *
	 * @param string $path Full path to PDF
	 *
	 * @return int
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
}

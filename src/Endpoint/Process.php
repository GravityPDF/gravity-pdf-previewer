<?php

namespace GFPDF\Plugins\Previewer\Endpoint;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Process
 *
 * @package GFPDF\Plugins\Previewer\Endpoint
 */
class Process {

	/**
	 * @since 2.0
	 */
	public function init() {
		add_action( 'parse_request', [ $this, 'endpoint_handler' ] );
	}

	/**
	 * Handle the iFrame mark-up endpoint
	 *
	 * @since 2.0
	 */
	public function endpoint_handler() {
		/* exit early if all the required URL parameters aren't met */
		$params = [
			'gpdf-viewer',
			'gpdf-viewer-token',
		];

		foreach ( $params as $param ) {
			if ( empty( $GLOBALS['wp']->query_vars[ $param ] ) ) {
				return;
			}
		}

		/* @TODO - validate TOKEN IS VALID */
		$token = $GLOBALS['wp']->query_vars['gpdf-viewer-token'];

		$pdf_url = rest_url( 'gravity-pdf-previewer/v1/pdf/' ) . urlencode( $token ) . '/';

		// @TODO - download option
		// @TODO - set pdf filename?

		$this->prevent_index();

		$path = plugin_dir_url( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/viewer/';

		/**
		 * Control the PDF Viewer default settings
		 *
		 * Available settings can be found at https://github.com/mozilla/pdf.js/blob/master/web/app_options.js#L29
		 *
		 * @Internal these options are output into JavaScript. Strings need to be explicitly wrapped in quotes
		 */
		$options = apply_filters(
			'gfpdf_previewer_pdfjs_default_settings',
			[
				'workerSrc'          => '"' . $path . 'build/pdf.worker.js' . '"',
				'disablePreferences' => 'true',
				'defaultUrl'         => '"' . $pdf_url . '"',
				'textLayerMode'      => '0',
			]
		);

		ob_start();
		include plugin_dir_path( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/viewer/web/viewer.php';
		ob_end_flush();

		$this->end();
	}

	/**
	 * @Internal For Unit Testing
	 *
	 * @since    2.0
	 */
	protected function end() {
		exit;
	}

	/**
	 * @since 2.0
	 */
	protected function prevent_index() {
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow', true );
		}
	}
}

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

		$this->prevent_index();

		ob_start();
		include plugin_dir_path( GFPDF_PDF_PREVIEWER_FILE ) . '/dist/viewer/web/viewer.php';
		$html = ob_get_clean();

		$html = str_replace( '{$PATH}', plugin_dir_url( GFPDF_PDF_PREVIEWER_FILE ) . '/dist/viewer/web/', $html );

		//@TODO - inject PDF URL into HTML and include URL params
		//@TODO - verify tmp PDF file exists
		//@TODO - allow viewer settings to be modified

		echo $html;

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
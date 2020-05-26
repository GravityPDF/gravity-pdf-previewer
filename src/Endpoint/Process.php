<?php

namespace GFPDF\Plugins\Previewer\Endpoint;

use GFPDF\Helper\Helper_Form;

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
			'gpdf-preview',
			'gpdf-preview-token',
		];

		foreach ( $params as $param ) {
			if ( empty( $GLOBALS['wp']->query_vars[ $param ] ) ) {
				return;
			}
		}

		$this->prevent_index();

		$html = file_get_contents( __DIR__ . '/../../dist/viewer/web/viewer.html');

		//@TODO - inject PDF URL into HTML and include URL params
		//@TODO - fix up viewer paths
		//@TODO - verify tmp PDF file exists
		//@TODO - allow viewer settings to be modified

		echo $html;
		$this->end();
	}

	/**
	 * @Internal For Unit Testing
	 *
	 * @since 2.0
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
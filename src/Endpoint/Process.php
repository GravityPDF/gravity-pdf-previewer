<?php

namespace GFPDF\Plugins\Previewer\Endpoint;

use GFPDF\Helper\Helper_Form;
use GFPDF\Plugins\Previewer\Exceptions\FieldNotFound;
use GFPDF\Plugins\Previewer\Exceptions\FormNotFound;
use GFPDF\Plugins\Previewer\Validation\Token;

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

	protected $gform;

	/**
	 * @var Token
	 */
	protected $token;

	public function __construct( Helper_Form $gform, Token $token ) {
		$this->gform    = $gform;
		$this->token = $token;
	}

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
		if ( empty( $GLOBALS['wp']->query_vars['gpdf-viewer'] ) ) {
			return;
		}

		$this->prevent_index();

		try {
			if ( ! isset( $_GET['token'] ) ) {
				throw new \Exception( 'Invalid Request' );
			}

			$token   = $_GET['token'];
			$pdf_url = add_query_arg( 'token', $token, rest_url( 'gravity-pdf-previewer/v2/pdf/' ) );

			list( $form_id, $field_id ) = $this->token->validate( rawurldecode( $token ) );

			$preview_field = $this->get_preview_field( $form_id, $field_id );
			$path          = plugin_dir_url( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/viewer/';

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
					'cursorToolOnLoad'   => '1',
				]
			);

			$this->load( [
				'options'  => $options,
				'path'     => $path,
				'download' => empty( $preview_field['pdfdownload'] ),
			] );

		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}

		$this->end();
	}

	protected function load( $data ) {
		extract( $data );

		ob_start();
		include plugin_dir_path( GFPDF_PDF_PREVIEWER_FILE ) . 'dist/viewer/web/viewer.php';
		ob_end_flush();
	}

	/* @TODO - move into own helper class */
	protected function get_preview_field( $form_id, $field_id ) {
		$form     = $this->gform->get_form(  $form_id );

		/* Couldn't get form info from ID */
		if ( $form === null ) {
			throw new FormNotFound( 'Invalid Request' );
		}

		foreach ( $form['fields'] as $field ) {
			if ( $field['id'] === $field_id && $field->type === 'pdfpreview' ) {
				return $field;
			}
		}

		/* Couldn't locate matching Previewer field in form */
		throw new FieldNotFound( 'Invalid Request' );
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

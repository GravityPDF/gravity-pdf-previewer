<?php

namespace GFPDF\Plugins\Previewer\Exceptions;

use Exception;

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
 * Class PDFNotActive
 *
 * @package GFPDF\Plugins\Previewer\Exceptions
 *
 * @since   0.1
 */
class PDFNotActive extends Exception {

	/**
	 * @var string
	 *
	 * @since 0.1
	 */
	protected $message = 'PDF Configuration not active';

	/**
	 * FormNotFound constructor.
	 *
	 * Append the form ID to our Exception message, if it was passed in
	 *
	 * @param null           $message
	 * @param int            $code
	 * @param Exception|null $previous
	 */
	public function __construct( $message = null, $code = 0, Exception $previous = null ) {
		if ( $message ) {
			$this->message .= ' #' . $message;
		}

		parent::__construct( $this->message, $code, $previous );
	}
}

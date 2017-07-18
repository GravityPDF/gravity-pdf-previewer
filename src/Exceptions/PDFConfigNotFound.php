<?php

namespace GFPDF\Plugins\Previewer\Exceptions;

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


class PDFConfigNotFound extends Exception {
	protected $message = 'Could not find PDF Configuration';

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
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
 * Class FieldNotFound
 *
 * @package GFPDF\Plugins\Previewer\Exceptions
 *
 * @since   0.1
 */
class FieldNotFound extends Exception {
}
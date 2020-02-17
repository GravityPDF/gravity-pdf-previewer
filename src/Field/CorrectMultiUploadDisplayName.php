<?php

namespace GFPDF\Plugins\Previewer\Field;

use GFPDF\Helper\Helper_Interface_Filters;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2019, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.6
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CorrectMultiUploadDisplayName
 *
 * @package GFPDF\Plugins\Previewer\Field
 */
class CorrectMultiUploadDisplayName implements Helper_Interface_Filters {

	/**
	 * Initialise our module
	 *
	 * @since 1.2.6
	 */
	public function init() {
		$this->add_filters();
	}

	/**
	 * @since 1.2.6
	 */
	public function add_filters() {
		add_filter( 'gfpdf_pdf_field_content_fileupload', [ $this, 'replace_tmp_filename' ], 10, 2 );
	}

	/**
	 * Replace the temporary filenames for the multiupload field when using the Previewer
	 *
	 * @param string $html
	 * @param GF_Field $field
	 *
	 * @return string
	 *
	 * @since 1.2.6
	 */
	public function replace_tmp_filename( $html, $field ) {
		if (
			defined( 'DOING_PDF_PREVIEWER' ) && DOING_PDF_PREVIEWER &&
			isset( $_POST['gform_uploaded_files'] )
		) {
			$field_files_array = (array) json_decode( stripslashes( $_POST['gform_uploaded_files'] ), true );

			if ( isset( $field_files_array[ 'input_' . $field->id ] ) ) {
				$pattern = '">%s</a>';

				if ( ! is_array( $field_files_array[ 'input_' . $field->id ] ) ) {
					$html = preg_replace( '/<a href="(.+?)">(.+?)<\/a>/', '<a href="$1">' . $field_files_array[ 'input_' . $field->id ] . '</a>', $html );
				} else {
					foreach ( $field_files_array[ 'input_' . $field->id ] as $file ) {
						$html = str_replace(
							sprintf( $pattern, $file['temp_filename'] ),
							sprintf( $pattern, $file['uploaded_filename'] ),
							$html
						);
					}
				}
			}
		}

		return $html;
	}
}

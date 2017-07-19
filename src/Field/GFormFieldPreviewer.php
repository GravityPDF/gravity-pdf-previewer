<?php

namespace GFPDF\Plugins\Previewer\Field;

use GF_Field;

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

/**
 * Class GFormFieldPreviewer
 *
 * @package GFPDF\Plugins\Previewer\Field
 */
class GFormFieldPreviewer extends GF_Field {

	/**
	 * @var string
	 *
	 * @since 0.1
	 */
	public $type = 'pdfpreview';

	/**
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'PDF Preview', 'gravity-pdf-previewer' );
	}

	/**
	 * @param array  $form
	 * @param string $value
	 * @param null   $entry
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$pdf_id         = ( isset( $this->pdfpreview ) ) ? $this->pdfpreview : 0;
		$preview_height = ( isset( $this->pdfpreviewheight ) && (int) $this->pdfpreviewheight > 0 ) ? (int) $this->pdfpreviewheight : 600;

		$content = '<div class="gpdf-previewer-wrapper" 						 
						 data-field-id="' . esc_attr( $this->id ) . '"
						 data-pdf-id="' . esc_attr( $pdf_id ) . '"
						 data-previewer-height="' . esc_attr( $preview_height ) . '">
							<!-- Placeholder -->
						</div>';

		if ( $this->is_entry_detail() || $this->is_form_editor() ) {
			$content = '<div class="gf-html-container">
							<span class="gf_blockheader">
								<i class="fa fa-file-pdf-o"></i> ' .
			           esc_html__( 'PDF Document', 'gravity-pdf-previewer' ) . '
							</span>
							<span>' .
			           esc_html__( 'This is a content placeholder. The PDF Preview is not displayed in the form admin. View the form to preview the PDF.', 'gravity-pdf-previewer' ) .
			           '</span>
						</div>';
		}

		return $content;
	}

	/**
	 * @since 0.1
	 */
	public function get_form_editor_field_settings() {
		return [
			'conditional_logic_field_setting',
			'css_class_setting',
			'label_setting',
			'description_setting',
			'pdf_selector_setting',
			'pdf_preview_height_setting',
			'pdf_watermark_setting',
		];
	}

	/**
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_form_editor_button() {
		return [
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		];
	}

	/**
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function is_conditional_logic_supported() {
		return true;
	}
}

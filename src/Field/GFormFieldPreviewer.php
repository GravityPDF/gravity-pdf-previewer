<?php

namespace GFPDF\Plugins\Previewer\Field;

use GF_Field;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF Previewer.

    Copyright (C) 2018, Blue Liquid Designs

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
 *
 * @since   0.1
 */
class GFormFieldPreviewer extends GF_Field {

	/**
	 * @var string
	 *
	 * @since 0.1
	 */
	public $type = 'pdfpreview';

	/**
	 * Mark this is a displayOnly field to prevent display in some of the settings
	 *
	 * @var bool
	 *
	 * @since 1.1
	 */
	public $displayOnly = true;

	/**
	 * Mask this field as a HTML field to prevent public display
	 *
	 * @return string
	 *
	 * @since 1.1
	 */
	public function get_input_type() {
		return 'html';
	}

	/**
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'PDF Preview', 'gravity-pdf-previewer' );
	}

	/**
	 *
	 *
	 * @param array  $form
	 * @param string $value
	 * @param null   $entry
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		ob_start();

		if ( $this->is_entry_detail() || $this->is_form_editor() ) {
			include __DIR__ . '/markup/previewer-placeholder.php';
		} else {
			$field_id       = $this->id;
			$pdf_id         = ( isset( $this->pdfpreview ) ) ? $this->pdfpreview : $this->get_pdf_id_if_any( $form );
			$preview_height = ( isset( $this->pdfpreviewheight ) && (int) $this->pdfpreviewheight > 0 ) ? (int) $this->pdfpreviewheight : 600;

			include __DIR__ . '/markup/previewer-wrapper.php';
		}

		return ob_get_clean();
	}

	/**
	 * Returns the first PDF ID, if it exists
	 *
	 * @param array $form
	 *
	 * @return string
	 *
	 * @since 0.2
	 */
	protected function get_pdf_id_if_any( $form ) {
		if ( isset( $form['gfpdf_form_settings'] ) && count( $form['gfpdf_form_settings'] ) > 0 ) {
			$pdf = reset( $form['gfpdf_form_settings'] );

			return $pdf['id'];
		}

		return 0;
	}

	/**
	 * Enable supported settings for our custom field
	 *
	 * @return array
	 *
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
	 * Add field to the Advanced group
	 *
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
}

<?php

namespace GFPDF\Plugins\Previewer\Field;

use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Interface_Filters;
use GPDFAPI;

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
 * Class RegisterPreviewerCustomFields
 *
 * @package GFPDF\Plugins\Previewer\Field
 */
class RegisterPreviewerCustomFields implements Helper_Interface_Actions, Helper_Interface_Filters {

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gform_field_standard_settings_25', [ $this, 'add_pdf_selector' ] );
		add_action( 'gform_editor_js', [ $this, 'editor_js' ] );
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gform_tooltips', [ $this, 'add_tooltips' ] );
	}

	/**
	 * @param array $tooltips
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_tooltips( $tooltips ) {

		/* @TODO */
		$tooltips['pdf_selector_setting'] = '<h6>' . esc_html__( 'PDFs', 'gravity-pdf-previewer' ) . '</h6>' . esc_html__( 'Add Description', 'gravity-pdf-previewer' );

		return $tooltips;
	}

	/**
	 * @param int $form_id
	 *
	 * @since 0.1
	 */
	public function add_pdf_selector( $form_id ) {
		$pdfs = GPDFAPI::get_form_pdfs( $form_id );

		if ( is_wp_error( $pdfs ) ) {
			return;
		}

		?>
        <li class="pdf_selector_setting field_setting">
            <label for="pdf_selector" class="section_label">
				<?php esc_html_e( 'PDF to Preview', 'gravity-pdf-previewer' ); ?>
				<?php gform_tooltip( 'pdf_selector_setting' ) ?>
            </label>

            <select id="pdf_selector" onchange="SetFieldProperty('pdf-preview', this.value)">
				<?php foreach ( $pdfs as $pdf ):
					if ( $pdf['active'] === true ): ?>
                        <option value="<?php echo $pdf['id']; ?>">
							<?php echo $pdf['name']; ?>
                        </option>
					<?php endif;
				endforeach; ?>
            </select>

        </li>
		<?php
	}

	public function editor_js() {
		?>
        <script type='text/javascript'>
          jQuery(document).bind("gform_load_field_settings", function (event, field) {
            if (field.type === 'pdf-preview') {
              jQuery("#pdf_selector").val(field['pdf-preview'])
            }
          })
        </script>
		<?php
	}
}

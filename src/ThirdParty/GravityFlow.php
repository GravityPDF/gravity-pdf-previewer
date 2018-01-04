<?php

namespace GFPDF\Plugins\Previewer\ThirdParty;

use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Interface_Filters;

use Gravity_Flow_Entry_Editor;
use GFAPI;
use GFCommon;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2017, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
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
 * Class GravityFlow
 *
 * @package GFPDF\Plugins\Previewer\Field
 *
 * @since   1.1
 */
class GravityFlow implements Helper_Interface_Filters, Helper_Interface_Actions {

	/**
	 * Determine if the GraivtyFlow workflow is currently underway
	 *
	 * @var bool
	 *
	 * @since 1.1
	 */
	protected $doing_workflow = false;

	/**
	 * Holds the Gravity Flow Entry Editor once initialised
	 *
	 * @var null
	 *
	 * @since 1.1
	 */
	protected $gravityflow_editor = null;

	/**
	 * Initiaise the class if Gravity Flow is activated
	 *
	 * @since 1.1
	 */
	public function init() {
		if ( class_exists( 'Gravity_Flow' ) && defined( 'GRAVITY_FLOW_VERSION' ) && version_compare( GRAVITY_FLOW_VERSION, '1.7', '>=' ) ) {
			$this->add_actions();
			$this->add_filters();
		}
	}

	/**
	 * @since 1.1
	 */
	public function add_actions() {
		add_action( 'gfpdf_previewer_start_pdf_generation', [ $this, 'start_previewer_generation' ] );
		add_action( 'gfpdf_previewer_end_pdf_generation', [ $this, 'end_previewer_generation' ] );
	}

	/**
	 * @since 1.1
	 */
	public function add_filters() {
		add_filter( 'gform_entry_field_value', [ $this, 'generate_preview_markup' ], 10, 4 );
		add_filter( 'gfpdf_previewer_created_entry', [ $this, 'merge_gravityflow_entry_data' ], 10, 4 );
		add_filter( 'gfpdf_previewer_form_id', [ $this, 'set_form_id' ], 10, 2 );
		add_filter( 'gravityflow_workflow_detail_display_field', [ $this, 'override_previewer_field_display' ], 10, 2 );
	}

	/**
	 * Override GravityFlow's HTML markup for our Previewer display field
	 *
	 * @param string   $value
	 * @param GF_Field $field
	 * @param array    $entry
	 * @param array    $form
	 *
	 * @return string
	 *
	 * @since 1.1
	 */
	public function generate_preview_markup( $value, $field, $entry, $form ) {
		if ( $field->type === 'pdfpreview' && ! empty( $field->gravityflow_is_display_field ) ) {
			$value = $field->get_field_input( $form, $value, $entry );
		}

		return $value;
	}

	/**
	 * Apply Gravity Flow form filters before the Preview PDF is generated
	 * This ensures the correct $_POST data is processed based on Gravity Flow's complex display system
	 *
	 * @param WP_REST_Request $request
	 *
	 * @since 1.1
	 */
	public function start_previewer_generation( $request ) {
		$gravityflow = gravity_flow();
		$input       = $request->get_body_params();

		if ( isset( $input['gravityflow_submit'] ) && isset( $input['gform_field_values'] ) ) {
			$this->include_classes();
			$this->reset_current_user();

			parse_str( $input['gform_field_values'], $field_values );
			$entry_id = ( isset( $field_values['id'] ) ) ? $field_values['id'] : 0;
			$form_id  = (int) $input['gravityflow_submit'];
			$step_id  = $input['step_id'];

			$form  = $this->get_form( $form_id );
			$entry = $this->get_entry( $entry_id );

			if ( is_wp_error( $form ) || is_wp_error( $entry ) ) {
				return;
			}

			$step = $gravityflow->get_step( $step_id, $entry );

			$this->gravityflow_editor = new Gravity_Flow_Entry_Editor( $form, $entry, $step, true );
			add_filter( 'gform_pre_render', [ $this->gravityflow_editor, 'filter_gform_pre_render' ], 999 );
		}
	}

	/**
	 * If the Gravity Flow Entry Editor class still exists after the Previewer has run we'll reset everything done in
	 * $this->start_previewer_generation()
	 *
	 * @since 1.1
	 */
	public function end_previewer_generation() {
		if ( $this->gravityflow_editor !== null ) {
			remove_filter( 'gform_pre_render', [ $this->gravityflow_editor, 'filter_gform_pre_render' ], 999 );
			$this->gravityflow_editor = null;
		}
	}

	/**
	 * To ensure accuracy in the previewer, when Gravity Flow Preview submission occurs we'll merge the existing
	 * DB entry with the $_POST data entry
	 *
	 * @param array $entry
	 * @param array $form
	 * @param array $settings
	 * @param array $input
	 *
	 * @return array
	 *
	 * @since 1.1
	 */
	public function merge_gravityflow_entry_data( $entry, $form, $settings, $input ) {
		if ( isset( $input['gravityflow_submit'] ) && isset( $input['gform_field_values'] ) ) {
			parse_str( $input['gform_field_values'], $field_values );
			$entry_id = ( isset( $field_values['id'] ) ) ? $field_values['id'] : 0;
			$step_id  = $input['step_id'];

			$entry = $this->merge_existing_entry( $entry, $entry_id, $step_id );
		}

		return $entry;
	}

	/**
	 * Use the correct form ID when using Gravity Flow
	 *
	 * @param int   $form_id
	 * @param array $input
	 *
	 * @return int
	 *
	 * @since 1.1
	 */
	public function set_form_id( $form_id, $input ) {
		if ( isset( $input['gravityflow_submit'] ) ) {
			$form_id = (int) $input['gravityflow_submit'];
		}

		return $form_id;
	}

	/**
	 * Ensure the Previewer field isn't removed from the Gravity Flow list when there's conditional logic on the field
	 * and the Display Fields option is set to "All fields"
	 *
	 * @param bool     $display
	 * @param GF_Field $field
	 *
	 * @return bool
	 *
	 * @since 1.1
	 */
	public function override_previewer_field_display( $display, $field ) {
		if ( $field->type === 'pdfpreview' ) {
			return true;
		}

		return $display;
	}

	/**
	 * Include the required classes for get access to the Gravity Flow editor
	 *
	 * @since 1.1
	 */
	protected function include_classes() {
		$gravityflow             = gravity_flow();
		$gravityflow_editor_file = $gravityflow->get_base_path() . '/includes/pages/class-entry-editor.php';
		$gravityform_display     = GFCommon::get_base_path() . '/form_display.php';

		foreach ( [ $gravityflow_editor_file, $gravityform_display ] as $file ) {
			if ( is_file( $file ) ) {
				require_once( $file );
			}
		}
	}

	/**
	 * Reset WordPress' current user to null
	 *
	 * @Internal the current user was being returned as "0" during the Gravity Flow call so we'll reset it
	 *
	 * @since    1.1
	 */
	protected function reset_current_user() {
		global $current_user;
		$current_user = null;
	}

	/**
	 * Merge the POST entry with the DB entry using the current Gravity Flow editable fields
	 *
	 * @param array $raw_entry
	 * @param int   $entry_id
	 * @param int   $step_id
	 *
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function merge_existing_entry( $raw_entry, $entry_id, $step_id ) {
		if ( $entry_id === 0 ) {
			return $raw_entry;
		}

		/* Get the current entry from the database */
		$db_entry = $this->get_entry( $entry_id );

		/* Return the POST data entry if we can't get the one from the DB */
		if ( is_wp_error( $db_entry ) ) {
			return $raw_entry;
		}

		$form = $this->get_form( $db_entry['form_id'] );

		/* If there's an issue getting the form we'll do a basic entry merge */
		if ( $form === false ) {
			return $this->fallback_entry_merge( $raw_entry, $db_entry );
		}

		/* Pull the editable fields from Gravity Flow and override the db entry with the POST data */
		$gravityflow     = gravity_flow();
		$step            = $gravityflow->get_step( $step_id, $db_entry );
		$editable_fields = $step->get_editable_fields();

		return $this->entry_merge( $db_entry, $raw_entry, $form['fields'], $editable_fields );
	}

	/**
	 * @param int $entry_id
	 *
	 * @return array|\WP_Error
	 *
	 * @since 1.1
	 */
	protected function get_entry( $entry_id ) {
		return GFAPI::get_entry( $entry_id );
	}

	/**
	 * @param int $form_id
	 *
	 * @return array|false
	 */
	protected function get_form( $form_id ) {
		return GFAPI::get_form( $form_id );
	}

	/**
	 * Merge in the editable fields values from the POSTed entry data to the DB entry data
	 *
	 * @param array $db_entry  The current entry information found in the DB
	 * @param array $raw_entry The newly $_POSTed entry information
	 * @param array $fields    The Gravity Form object fields
	 * @param array $editable_fields
	 *
	 * @return array
	 *
	 * @since 1.1
	 */
	protected function entry_merge( $db_entry, $raw_entry, $fields, $editable_fields ) {
		foreach ( $fields as $field ) {
			if ( in_array( $field->id, $editable_fields ) ) {
				$inputs = $field->get_entry_inputs();

				if ( is_array( $inputs ) ) {
					foreach ( $inputs as $input ) {
						$db_entry[ $input['id'] ] = $raw_entry[ $input['id'] ];
					}
				} else {
					$db_entry[ $field->id ] = $raw_entry[ $field->id ];
				}
			}
		}

		return $db_entry;
	}

	/**
	 * Fallback entry merge for when we cannot get the Gravity Form from the database
	 *
	 * @Internal this function doesn't take into account the editable fields Gravity Flow is displaying
	 *
	 * @param $raw_entry
	 * @param $db_entry
	 *
	 * @return array
	 *
	 * @since    1.1
	 */
	protected function fallback_entry_merge( $raw_entry, $db_entry ) {
		$cleaned_entry = array_filter( $raw_entry );
		return array_replace_recursive( $db_entry, $cleaned_entry );
	}
}
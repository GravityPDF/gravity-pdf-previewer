<?php

namespace GFPDF\Plugins\Previewer;

use GFPDF\Plugins\Previewer\API\PdfViewerApiResponse;
use GFPDF\Plugins\Previewer\Field\RegisterPreviewerField;
use GFPDF\Plugins\Previewer\Field\RegisterPreviewerCustomFields;
use GFPDF\Plugins\Previewer\API\RegisterPdfGeneratorAPIEndpoint;
use GFPDF\Plugins\Previewer\API\RegisterPdfViewerAPIEndpoint;
use GFPDF\Plugins\Previewer\API\PdfGeneratorApiResponse;

use GFPDF\Helper\Licensing\EDD_SL_Plugin_Updater;
use GFPDF\Helper\Helper_Abstract_Addon;
use GFPDF\Helper\Helper_Singleton;
use GFPDF\Helper\Helper_Logger;
use GFPDF\Helper\Helper_Notices;

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

/* Load Composer */
require_once( __DIR__ . '/../vendor/autoload.php' );

/**
 * Class Bootstrap
 *
 * @package GFPDF\Plugins\Previewer
 */
class Bootstrap extends Helper_Abstract_Addon {

	/**
	 * Initialise the plugin classes and pass them to our parent class to
	 * handle the rest of the bootstrapping (licensing ect)
	 *
	 * @param array $classes An array of classes to store in our singleton
	 *
	 * since 0.1
	 */
	public function init( $classes = [] ) {

		$data          = GPDFAPI::get_data_class();
		$pdf_save_path = $data->template_tmp_location . 'previewer/';

		/* Register our classes and pass back up to the parent initialiser */
		$classes = array_merge( $classes, [
			new RegisterPreviewerCustomFields(),
			new RegisterPreviewerField(),

			new RegisterPdfGeneratorAPIEndpoint(
				new PdfGeneratorApiResponse(
					GPDFAPI::get_mvc_class( 'Model_PDF' ),
					$pdf_save_path
				)
			),

			new RegisterPdfViewerAPIEndpoint( new PdfViewerApiResponse( $pdf_save_path ) ),
		] );

		/* Include links on plugin page */
		add_action( 'after_plugin_row_' . plugin_basename( GFPDF_PDF_PREVIEWER_FILE ), [
			$this,
			'license_registration',
		] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );

		/* Run the setup */
		parent::init( $classes );
	}

	/**
	 * Check the plugin's license is active and initialise the EDD Updater
	 *
	 * since 0.1
	 */
	public function plugin_updater() {

		/* Skip over this addon if license status isn't active */
		$license_info = $this->get_license_info();
		if ( $license_info['status'] !== 'active' ) {
			return;
		}

		new EDD_SL_Plugin_Updater(
			$this->data->store_url,
			$this->get_main_plugin_file(),
			[
				'version'   => $this->get_version(),
				'license'   => $license_info['license'],
				'item_name' => $this->get_short_name(),
				'author'    => $this->get_author(),
				'beta'      => false,
			]
		);

		$this->log->notice( sprintf( '%s plugin updater initialised', $this->get_name() ) );
	}

	/**
	 * since 0.1
	 */
	public function license_registration() {

		$license_info = $this->get_license_info();
		if ( $license_info['status'] === 'active' ) {
			return;
		}

		?>

        <tr class="plugin-update-tr">
            <td colspan="3" class="plugin-update colspanchange">
                <div class="update-message">
					<?php
					printf(
						esc_html__(
							'%sRegister your copy of Gravity PDF Previewer%s to receive access to automatic upgrades and support. Need a license key? %sPurchase one now%s.',
							'gravity-forms-pdf-extended'
						),
						'<a href="' . admin_url( 'admin.php?page=gf_settings&subview=PDF&tab=license' ) . '">', '</a>',
						'<a href="' . esc_url( 'https://gravitypdf.com/checkout/?edd_action=add_to_cart&download_id=14971' ) . '">', '</a>'
					)
					?>
                </div>
            </td>
        </tr>
		<?php
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta
	 * @param mixed $file  Plugin Base file
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function plugin_row_meta( $links, $file ) {

		if ( $file === plugin_basename( GFPDF_PDF_PREVIEWER_FILE ) ) {
			$row_meta = [
				'docs'    => '<a href="' . esc_url( 'https://gravitypdf.com/documentation/v4/shop-plugin-previewer-add-on/' ) . '" title="' . esc_attr__( 'View plugin Documentation', 'gravity-pdf-previewer' ) . '">' . esc_html__( 'Docs', 'gravity-forms-pdf-extended' ) . '</a>',
				'support' => '<a href="' . esc_url( 'https://gravitypdf.com/support/#contact-support' ) . '" title="' . esc_attr__( 'Get Help and Support', 'gravity-forms-pdf-extended' ) . '">' . esc_html__( 'Support', 'gravity-forms-pdf-extended' ) . '</a>',
			];

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}
}

/* Use the filter below to replace and extend our Bootstrap class if needed */
$name = 'Gravity PDF Previewer';
$slug = 'gravity-pdf-previewer';

$plugin = apply_filters( 'gfpdf_previewer_initialise', new Bootstrap(
	$slug,
	$name,
	'Gravity PDF',
	GFPDF_PDF_PREVIEWER_VERSION,
	GFPDF_PDF_PREVIEWER_FILE,
	GPDFAPI::get_data_class(),
	GPDFAPI::get_options_class(),
	new Helper_Singleton(),
	new Helper_Logger( $slug, $name ),
	new Helper_Notices()
) );

$plugin->init();

/* Use the action below to access our Bootstrap class, and any singletons saved in $plugin->singleton */
do_action( 'gfpdf_previewer_bootrapped', $plugin );
<?php

namespace GFPDF\Plugins\Previewer;

use GFPDF\Plugins\Previewer\API\PdfViewerApiResponse;
use GFPDF\Plugins\Previewer\Endpoint\Process as EndpointProcess;
use GFPDF\Plugins\Previewer\Endpoint\Register as EndpointRegister;
use GFPDF\Plugins\Previewer\Field\RegisterPreviewerField;
use GFPDF\Plugins\Previewer\Field\RegisterPreviewerCustomFields;
use GFPDF\Plugins\Previewer\Field\CorrectMultiUploadDisplayName;
use GFPDF\Plugins\Previewer\API\RegisterPdfGeneratorAPIEndpoint;
use GFPDF\Plugins\Previewer\API\RegisterPdfViewerAPIEndpoint;
use GFPDF\Plugins\Previewer\API\PdfGeneratorApiResponse;
use GFPDF\Plugins\Previewer\Field\SkipPdfPreviewerField;
use GFPDF\Plugins\Previewer\ThirdParty\GravityFlow;
use GFPDF\Plugins\Previewer\ThirdParty\NestedFormsPerk;
use GFPDF\Plugins\Previewer\ThirdParty\WooCommerceGravityForms;

use GFPDF\Helper\Licensing\EDD_SL_Plugin_Updater;
use GFPDF\Helper\Helper_Abstract_Addon;
use GFPDF\Helper\Helper_Singleton;
use GFPDF\Helper\Helper_Logger;
use GFPDF\Helper\Helper_Notices;

use GPDFAPI;

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
		$pdf_generator_api = new PdfGeneratorApiResponse( GPDFAPI::get_mvc_class( 'Model_PDF' ), $pdf_save_path );
		$pdf_viewer_api    = new PdfViewerApiResponse( $pdf_save_path );

		$classes = array_merge(
			$classes,
			[
				$pdf_generator_api,
				$pdf_viewer_api,
				new RegisterPreviewerCustomFields(),
				new RegisterPreviewerField(),
				new RegisterPdfGeneratorAPIEndpoint( $pdf_generator_api ),
				new RegisterPdfViewerAPIEndpoint( $pdf_viewer_api ),
				new SkipPdfPreviewerField(),
				new GravityFlow(),
				new WooCommerceGravityForms(),
				new CorrectMultiUploadDisplayName(),
				new NestedFormsPerk(),
				new EndpointRegister(),
				new EndpointProcess(),
			]
		);			

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
}

/* Use the filter below to replace and extend our Bootstrap class if needed */
$name = 'Gravity PDF Previewer';
$slug = 'gravity-pdf-previewer';

$plugin = apply_filters(
	'gfpdf_previewer_initialise',
	new Bootstrap(
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
	)
);

$plugin->set_edd_download_id( '14971' );
$plugin->set_addon_documentation_slug( 'shop-plugin-previewer-add-on' );
$plugin->init();

/* Use the action below to access our Bootstrap class, and any singletons saved in $plugin->singleton */
do_action( 'gfpdf_previewer_bootrapped', $plugin );

<?php

/**
 * Plugin Name:     Gravity PDF Previewer
 * Plugin URI:      https://gravitypdf.com/shop/previewer-add-on/
 * Description:     Allow Gravity PDF documents to be previewed before a Gravity Form has been submitted. Includes live reloading of preview and watermark support.
 * Author:          Gravity PDF
 * Author URI:      https://gravitypdf.com
 * Text Domain:     gravity-pdf-previewer
 * Domain Path:     /languages
 * Version:         1.2.9
 */

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GFPDF_PDF_PREVIEWER_FILE', __FILE__ );
define( 'GFPDF_PDF_PREVIEWER_VERSION', '1.2.9' );

/**
 * Class GPDF_Previewer_Checks
 *
 * @since 0.1
 */
class GPDF_Previewer_Checks {

	/**
	 * Holds any blocker error messages stopping plugin running
	 *
	 * @var array
	 *
	 * @since 0.1
	 */
	private $notices = [];

	/**
	 * @var string
	 *
	 * @since 0.1
	 */
	private $required_gravitypdf_version = '4.3.0-beta1';

	/**
	 * Run our pre-checks and if it passes bootstrap the plugin
	 *
	 * @return void
	 *
	 * @since 0.1
	 */
	public function init() {

		/* Test the minimum version requirements are met */
		$this->check_gravitypdf_version();

		/* Check if any errors were thrown, enqueue them and exit early */
		if ( sizeof( $this->notices ) > 0 ) {
			add_action( 'admin_notices', [ $this, 'display_notices' ] );

			return null;
		}

		add_action( 'gfpdf_fully_loaded', function() {
			require_once __DIR__ . '/src/bootstrap.php';
        } );
	}

	/**
	 * Check if the current version of Gravity PDF is compatible with this add-on
	 *
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function check_gravitypdf_version() {

		/* Check if the Gravity PDF Minimum version requirements are met */
		if ( defined( 'PDF_EXTENDED_VERSION' ) &&
		     version_compare( PDF_EXTENDED_VERSION, $this->required_gravitypdf_version, '>=' )
		) {
			return true;
		}

		/* Throw error */
		$this->notices[] = sprintf( esc_html__( 'Gravity PDF Version %s or higher is required to use this add-on. Please install/upgrade Gravity PDF to the latest version.', 'gravity-pdf-previewer' ), $this->required_gravitypdf_version );
	}

	/**
	 * Helper function to easily display error messages
	 *
	 * @return void
	 *
	 * @since 0.1
	 */
	public function display_notices() {
		?>
        <div class="error">
            <p>
                <strong><?php esc_html_e( 'Gravity PDF Previewer Installation Problem', 'gravity-pdf-previewer' ); ?></strong>
            </p>

            <p><?php esc_html_e( 'The minimum requirements for the Gravity PDF Previewer plugin have not been met. Please fix the issue(s) below to continue:', 'gravity-pdf-previewer' ); ?></p>
            <ul style="padding-bottom: 0.5em">
				<?php foreach ( $this->notices as $notice ) : ?>
                    <li style="padding-left: 20px;list-style: inside"><?php echo $notice; ?></li>
				<?php endforeach; ?>
            </ul>
        </div>
		<?php
	}
}

/* Initialise the software */
add_action( 'plugins_loaded', function() {
	$gravitypdf_previewer = new GPDF_Previewer_Checks();
	$gravitypdf_previewer->init();
} );

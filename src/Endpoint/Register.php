<?php

namespace GFPDF\Plugins\Previewer\Endpoint;

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Register
 *
 * @package GFPDF\Plugins\Previewer\Endpoint
 */
class Register {

	/**
	 * @since 2.0
	 */
	public function init() {
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'register_rewrite_tags' ] );
	}

	/**
	 * @since 2.0
	 */
	public function add_rewrite_rules() {
		global $wp_rewrite;

		/* Create two regex rules to account for users with "index.php" in the URL */
		$permalink  = 'pdf-viewer/([A-Za-z0-9]+)/?';
		$rewrite_to = 'index.php?gpdf-viewer=1&gpdf-viewer-token=$matches[1]';

		$query = [
			'^' . $permalink,
			'^' . $wp_rewrite->index . '/' . $permalink,
		];

		/* Add our main endpoint */
		add_rewrite_rule( $query[0], $rewrite_to, 'top' );
		add_rewrite_rule( $query[1], $rewrite_to, 'top' );

		$this->maybe_flush_rewrite_rules( $query );
	}

	/**
	 * Register endpoint tags
	 *
	 * @param array $tags
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public function register_rewrite_tags( $tags ) {
		global $wp;

		/* Conditionally register rewrite tags to prevent conflict with other plugins */
		if (
			! empty( $_GET['gpdf-viewer'] ) ||
			strpos( $wp->matched_query, 'gpdf-viewer=1' ) === 0
		) {
			$tags[] = 'gpdf-viewer';
			$tags[] = 'gpdf-viewer-token';
		}

		return $tags;
	}

	/**
	 * Auto flush rewrite rules, if needed
	 *
	 * @param array $regex
	 *
	 * @since 2.0
	 */
	protected function maybe_flush_rewrite_rules( $regex ) {
		$rules = get_option( 'rewrite_rules' );

		foreach ( $regex as $rule ) {
			if ( ! isset( $rules[ $rule ] ) ) {
				flush_rewrite_rules( false );
				break;
			}
		}
	}
}
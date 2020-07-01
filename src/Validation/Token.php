<?php

namespace GFPDF\Plugins\Previewer\Validation;

use BadMethodCallException;
use GFCommon;
use GPDFAPI;
use InvalidArgumentException;

class Token {

	protected $pdf_path;

	public function __construct( $pdf_path ) {
		$this->pdf_path = $pdf_path;
	}

	public function create( $data ) {
		$token = implode( '|', $data );

		$secret_key = GPDFAPI::get_plugin_option( 'signed_secret_token', '' );

		/* If no secret key exists, generate it */
		if ( empty( $secret_key ) ) {
			$secret_key = wp_generate_password( 64 );
			GPDFAPI::update_plugin_option( 'signed_secret_token', $secret_key );
		}

		return GFCommon::openssl_encrypt( $token, $secret_key );
	}

	/**
	 * Decode token and verify the data is valid
	 *
	 * @param string $token
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public function validate( $token ) {
		$token       = GFCommon::openssl_decrypt( $token, GPDFAPI::get_plugin_option( 'signed_secret_token', '' ) );
		$token_array = explode( '|', $token );

		if ( count( $token_array ) !== 4 ) {
			throw new BadMethodCallException( 'Invalid Request' );
		}

		$form_id  = (int) $token_array[0];
		$field_id = (int) $token_array[1];
		$tmp_id   = preg_replace( '/[^A-Za-z0-9]/', '', $token_array[2] );
		$pdf_name = $token_array[3];

		if ( ! is_file( $this->pdf_path . "$tmp_id/$tmp_id.pdf" ) ) {
			throw new InvalidArgumentException( 'Invalid Request' );
		}

		if ( substr( $pdf_name, -4 ) !== '.pdf' ) {
			throw new InvalidArgumentException( 'Invalid Request' );
		}

		return [ $form_id, $field_id, $tmp_id, $pdf_name ];
	}
}
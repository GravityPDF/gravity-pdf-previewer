<div class="gpdf-previewer-wrapper"
	 data-field-id="<?php echo esc_attr( $field_id ); ?>"
	 data-pdf-id="<?php echo esc_attr( $pdf_id ); ?>"
	 data-previewer-height="<?php echo esc_attr( $preview_height ); ?>"
		<?php
		if ( (int) $download === 1 ):
			?>
			data-download="<?php echo esc_attr( $download ); ?>"<?php endif; ?>>
	<!-- Placeholder -->
</div>

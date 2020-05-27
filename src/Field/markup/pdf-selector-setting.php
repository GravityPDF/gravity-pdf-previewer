<li class="pdf_selector_setting field_setting">
	<label for="pdf_selector" class="section_label">
		<?php esc_html_e( 'PDF to Preview', 'gravity-pdf-previewer' ); ?>
		<?php gform_tooltip( 'pdf_selector_setting' ); ?>
	</label>

	<?php if ( count( $pdfs ) > 0 ): ?>
		<select id="pdf_selector" onchange="SetFieldProperty('pdfpreview', this.value)" style="min-width:250px">
			<?php foreach ( $pdfs as $pdf ): ?>
				<option value="<?php echo $pdf['id']; ?>">
					<?php echo $pdf['name']; ?>
				</option>
			<?php endforeach; ?>
		</select>
	<?php else: //phpcs:disable ?>
		<?php
		printf(
			__( 'To use this field you %1$sneed to create/active a PDF for this form%2$s.', 'gravity-pdf-previewer' ),
			'<a href="' . esc_url( $form_pdf_settings ) . '">',
			'</a>'
		);
		?>
	<?php endif; ?>
</li>

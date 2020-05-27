<li class="pdf_preview_height_setting field_setting">
	<label for="pdf_preview_height" class="section_label">
		<?php esc_html_e( 'Preview Height', 'gravity-pdf-previewer' ); ?>
		<?php gform_tooltip( 'pdf_preview_height' ); ?>
	</label>

	<input
			id="pdf_preview_height"
			type="number"
			style="width: 95px"
			onchange="SetFieldProperty('pdfpreviewheight', this.value)"/> px
</li>

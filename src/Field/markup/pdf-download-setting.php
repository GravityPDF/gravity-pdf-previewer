<li class="pdf_download_setting field_setting">
    <label class="section_label"><?php esc_html_e( 'Download Preview', 'gravity-pdf-previewer' ) ?></label>
    <input type="checkbox"
           id="pdf-download-setting"
           onclick="SetFieldProperty('pdfdownload', this.checked)"/>

    <label for="pdf-download-setting" class="inline">
		<?php esc_html_e( 'Allow user to download the PDF Preview?', 'gravity-pdf-previewer' ); ?>
    </label>
</li>
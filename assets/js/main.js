import $ from 'jquery'
import Generator from './Previewer/Generator'
import Viewer from './Previewer/Viewer'

require('../scss/previewer.scss')

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2017, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

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
 * The JS entry point for Webpack
 *
 * @since 0.1
 */
$(document).bind('gform_post_render', function (e, formId) {
  const $form = $('#gform_' + formId)

  /* Find each PDF Preview container in the form and initialise */
  $form.find('.gpdf-previewer-wrapper').each(function () {

    let fieldId = parseInt($(this).data('field-id'))
    let pdfId = $(this).data('pdf-id')
    let previewerHeight = parseInt($(this).data('previewer-height'))
    let download = (typeof $(this).data('download') !== 'undefined') ? parseInt($(this).data('download')) : 0;

    /* Continue to next matched element if no PDF ID exists */
    if (pdfId == 0) {
      return true
    }

    /* Set the minimum wrapper height to the size of the PDF Previewer height */
    $(this).css('min-height', previewerHeight + 'px')

    /* Initialise our Viewer / Generator classes */
    let viewer = new Viewer({
      viewerHeight: previewerHeight + 'px',
      viewer: PdfPreviewerConstants.viewerUrl,
      documentUrl: PdfPreviewerConstants.documentUrl,
      download
    })

    let previewer = new Generator({
      form: $form,
      container: $(this),
      endpoint: PdfPreviewerConstants.pdfGeneratorEndpoint + pdfId + '/' + fieldId + '/',
      viewer: viewer
    })

    previewer.init()
  })
})
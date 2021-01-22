import $ from 'jquery'
import Generator from './Previewer/Generator'
import Viewer from './Previewer/Viewer'

require('../scss/previewer.scss')

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.1
 */

/**
 * The JS entry point for Webpack
 *
 * @since 0.1
 */
$(document).bind('gform_post_render', function (e, formId) {
  /* Don't run if currently submitting the form */
  if (window['gf_submitting_' + formId]) {
    return
  }

  let $form = $('#gform_' + formId)

  /* Try match a slightly different mark-up */
  if ($form.length == 0) {
    $form = $('#gform_wrapper_' + formId).closest('form')
  }

  $form.data('fid', formId)

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
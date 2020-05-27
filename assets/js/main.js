import Generator from './Previewer/Generator'
import Viewer from './Previewer/Viewer'
import { previewerWrapper } from './Previewer/utilities/previewerWrapper'
require('../scss/previewer.scss')

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/**
 * The JS entry point for Webpack
 *
 * @since 0.1
 */
jQuery(document).bind('gform_post_render', function (e, formId, currentPage) {
  /* Don't run if currently submitting the form */
  if (window['gf_submitting_' + formId]) {
    return false
  }

  let form = document.getElementById('gform_' + formId)

  /* Try match a slightly different mark-up */
  if (form.length === 0) {
    form = document.querySelector(`#gform_wrapper_${formId}`).closest('form')
  }

  /* Find each PDF Preview container in the form and initialise */
  const multipleFormPages = form.querySelector(`#gform_page_${formId}_${currentPage}`)
  const container = previewerWrapper(multipleFormPages, form)
  const elem = [].slice.call(container)

  elem.map(item => {
    const fieldId = item.getAttribute('data-field-id')
    const pdfId = item.getAttribute('data-pdf-id')
    const previewerHeight = item.getAttribute('data-previewer-height')
    const download = item.getAttribute('data-download') != null ? item.getAttribute('data-download') : '0'

    /* Continue to next matched element if no PDF ID exists */
    if (pdfId === '0') {
      return true
    }

    /* Set the minimum wrapper height to the size of the PDF Previewer height */
    item.setAttribute('style', `min-height:${previewerHeight}px`)

    /* Initialise our Viewer / Generator classes */
    const viewer = new Viewer({
      viewerHeight: previewerHeight + 'px',
      viewer: PdfPreviewerConstants.viewerUrl,
      documentUrl: PdfPreviewerConstants.documentUrl,
      download
    })

    const previewer = new Generator({
      form: form,
      formId: formId,
      container: item,
      endpoint: PdfPreviewerConstants.pdfGeneratorEndpoint + pdfId + '/' + fieldId + '/',
      viewer: viewer
    })

    previewer.init()
  })
})

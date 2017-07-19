import $ from 'jquery'
import PdfPreviewGenerator from './Previewer/Generator'
import PdfPreviewViewer from './Previewer/Viewer'

require('../scss/previewer.scss')

$(document).bind('gform_post_render', function (e, formId) {
  const $form = $('#gform_' + formId)

  /* Find each PDF Preview container in the form and initialise */
  $form.find('.gpdf-previewer-wrapper').each(function () {

    let fId = $(this).data('field-id')
    let pdfId = $(this).data('pdf-id')
    let previewerHeight = $(this).data('previewer-height')
    
    let viewer = new PdfPreviewViewer({
      viewerHeight: previewerHeight + 'px',
      viewer: PdfPreviewerConstants.viewerUrl,
      documentUrl: PdfPreviewerConstants.documentUrl
    })

    let previewer = new PdfPreviewGenerator({
      form: $form,
      container: $(this),
      endpoint: PdfPreviewerConstants.pdfGeneratorEndpoint + pdfId + '/' + fId + '/',
      viewer: viewer
    })

    previewer.init()
  })
});
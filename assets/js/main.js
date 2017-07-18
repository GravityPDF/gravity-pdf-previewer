import $ from 'jquery'
import PdfPreviewGenerator from './Previewer/Generator'
import PdfPreviewViewer from './Previewer/Viewer'

require('../scss/previewer.scss')

$(document).bind('gform_post_render', function (e, formId) {
  const $form = $('#gform_' + formId)

  /* Find each PDF Preview container in the form and initialise */
  $form.find('.gpdf-previewer-wrapper').each(function () {

    let pdf_id = $(this).data('pdf-id')

    /* @TODO */
    let viewer = new PdfPreviewViewer({
      viewerHeight: '600px',
      viewer: 'http://local.wordpress.dev/wp-content/plugins/gravity-pdf-previewer/dist/viewer/web/viewer.html?file=',
      documentUrl: 'http://local.wordpress.dev/wp-json/gravity-pdf-previewer/v1/pdf/'
    })

    let previewer = new PdfPreviewGenerator({
      form: $form,
      container: $(this),
      endpoint: 'http://local.wordpress.dev/wp-json/gravity-pdf-previewer/v1/preview/' + pdf_id,
      viewer: viewer
    })

    previewer.init()
  })
});
(function ($) {

  function setupWatermarkToggle (field) {
    $('#pdf-watermark-setting').prop('checked', field['pdfwatermarktoggle'] == true)

    if (field['pdfwatermarktoggle'] != true) {
      $('#pdf_watermark_container').hide()
    }

    $('#pdf-watermark-setting').click(function () {
      $('#pdf_watermark_container').slideToggle()
    })
  }

  function setupWatermarkText (field) {
    $("#pdf_watermark_text").val(field['pdfwatermarktext'])
  }

  function setupPdfSelector (field) {
    if ($("#pdf_selector option[value='" + field['pdfpreview'] + "']").length > 0) {
      $("#pdf_selector").val(field['pdfpreview'])
    }

    $("#pdf_selector").trigger('change')
  }

  function setupPreviewHeight (field) {
    $("#pdf_preview_height").val(field['pdfpreviewheight'])
  }

  function setupWatermarkFont (field) {
    if ($("#pdf_watermark_font option[value='" + field['pdfwatermarkfont'] + "']").length > 0) {
      $("#pdf_watermark_font").val(field['pdfwatermarkfont'])
    }

    $("#pdf_watermark_font").trigger('change')
  }

  $(document).bind("gform_load_field_settings", function (event, field) {
    if (field.type === 'pdfpreview') {
      setupPdfSelector(field)
      setupPreviewHeight(field)
      setupWatermarkToggle(field)
      setupWatermarkText(field)
      setupWatermarkFont(field)
    }
  })
})(jQuery)
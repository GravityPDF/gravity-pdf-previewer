import $ from 'jquery'
const spinner = require('svg-url-loader?noquotes!../../images/spinner.svg');

export default class {

  addSpinner ($elm) {
    this.removeSpinner()

    this.$spinner = $('<div>').addClass('gpdf-spinner')

    this.$spinner.append($('<img>')
      .attr('src', spinner)
      .height(50))

    this.$spinner.append(PdfPreviewerConstants.loadingMessage)

    $elm.append(this.$spinner)
  }

  removeSpinner () {
    if (this.$spinner) {
      this.$spinner.remove()
    }
  }

  showLoadingError () {
    if (this.$spinner) {
      this.$spinner.html(PdfPreviewerConstants.errorMessage)
    }
  }
}
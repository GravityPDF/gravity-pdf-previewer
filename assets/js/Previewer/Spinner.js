import $ from 'jquery'

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.1
 */

/**
 * AJAX Loading class
 *
 * @since 0.1
 */
export default class {

  /**
   * Adds a loading spinner and message to the DOM
   *
   * @param jQuery $container
   *
   * @since 0.1
   */
  add ($container) {
    const spinner = require('svg-url-loader?noquotes!../../images/spinner.svg')

    this.remove()

    this.$spinner = $('<div>').addClass('gpdf-spinner')

    this.$spinner.append($('<img>')
      .attr('src', spinner)
      .height(50))

    this.$spinner.append(PdfPreviewerConstants.loadingMessage)

    $container.append(this.$spinner)
  }

  /**
   * If the spinner exists, remove it from the DOM
   *
   * @since 0.1
   */
  remove () {
    if (this.$spinner) {
      this.$spinner.remove()
    }
  }

  /**
   * If the spinner exists, replace it with a loading error message
   *
   * @since 0.1
   */
  showLoadingError () {
    if (this.$spinner) {
      this.$spinner.html(PdfPreviewerConstants.errorMessage)
    }
  }
}
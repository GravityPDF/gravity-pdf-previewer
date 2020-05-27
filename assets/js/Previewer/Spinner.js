/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
   * @param element container
   *
   * @since 0.1
   */
  add (container) {
    const spinner = require('svg-url-loader?noquotes!../../images/spinner.svg') // eslint-disable-line

    this.spinner = document.createElement('div')
    this.spinner.classList.add('gpdf-spinner')

    const img = document.createElement('img')
    img.setAttribute('src', spinner)
    img.setAttribute('style', 'height: 50px;')

    this.spinner.appendChild(img)

    const text = document.createTextNode(PdfPreviewerConstants.loadingMessage)

    this.spinner.appendChild(text)

    container.appendChild(this.spinner)
  }

  /**
   * If the spinner exists, remove it from the DOM
   *
   * @since 0.1
   */
  remove () {
    if (this.spinner) {
      this.spinner.remove()
    }
  }

  /**
   * If the spinner exists, replace it with a loading error message
   *
   * @since 0.1
   */
  showLoadingError () {
    if (this.spinner) {
      this.spinner.innerHTML = PdfPreviewerConstants.errorMessage
    }
  }
}

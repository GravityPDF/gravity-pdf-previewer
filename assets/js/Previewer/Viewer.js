/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/**
 * PDF Viewer class
 *
 * @since 0.1
 */
export default class {
  /**
   * @param args
   * .viewerHeight string The height of the iFrame
   * .viewer string The URL to the viewer
   *
   * @since 0.1
   */
  constructor (args) {
    this.viewerHeight = args.viewerHeight
    this.viewerUrl = args.viewer
  }

  /**
   * Creates our iFrame with the PDF Viewer
   *
   * @param token
   *
   * @returns <iframe />
   *
   * @since 0.1
   */
  create (token) {
    const pdfUrl = this.viewerUrl.replace('{TOKEN}', encodeURIComponent(token))

    this.remove()
    this.iframe = document.createElement('iframe')
    this.iframe.setAttribute('src', pdfUrl)
    this.iframe.setAttribute('frameborder', '0')
    this.iframe.setAttribute('width', '100%')
    this.iframe.setAttribute('height', this.viewerHeight)

    return this.iframe
  }

  /**
   * If the iFrame exists, remove it
   *
   * @since 0.1
   */
  remove () {
    if (this.doesViewerExist()) {
      this.iframe.remove()
      this.iframe = undefined
    }
  }

  /**
   * Check if the iFrame exists
   *
   * @returns {boolean}
   *
   * @since 0.1
   */
  doesViewerExist () {
    return this.iframe !== undefined
  }
}

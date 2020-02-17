import $ from 'jquery'

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
   *            .viewerHeight int The height of the iFrame
   *            .viewer string The URL to the viewer
   *            .documentUrl string The REST API endpoint to stream the generated PDF
   *
   * @since 0.1
   */
  constructor (args) {
    this.viewerHeight = args.viewerHeight

    this.viewerUrl = args.viewer
    this.documentUrl = args.documentUrl
    this.download = args.download
  }

  /**
   * Creates our iFrame with the PDF Viewer
   *
   * @param id
   *
   * @returns jQuery
   *
   * @since 0.1
   */
  create (id) {
    let pdfUrl = this.viewerUrl + '?file=' + encodeURIComponent(this.documentUrl + id)

    if (this.download === 1) {
      pdfUrl = this.viewerUrl + '?download=1&file=' + encodeURIComponent(this.documentUrl + id + '?download=1')
    }

    this.remove()
    this.$iframe = $('<iframe>')
      .attr('src', pdfUrl)
      .attr('frameborder', 0)
      .width('100%')
      .height(this.viewerHeight)

    return this.$iframe
  }

  /**
   * If the iFrame exists, remove it
   *
   * @since 0.1
   */
  remove () {
    if (this.doesViewerExist()) {
      this.$iframe.remove()
      this.$iframe = undefined
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
    return this.$iframe !== undefined
  }
}
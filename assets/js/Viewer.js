import $ from 'jquery'

export default class {

  constructor (args) {
    this.viewerHeight = args.viewerHeight

    this.viewerUrl = args.viewer
    this.documentUrl = args.documentUrl
  }

  generateIframe (id) {
    this.removeIframe()
    this.$iframe = $('<iframe>')
      .attr('src', this.viewerUrl + this.documentUrl + id)
      .attr('frameborder', 0)
      .width('100%')
      .height(this.viewerHeight)

    return this.$iframe
  }

  removeIframe () {
    if (this.doesViewerExist()) {
      this.$iframe.remove()
      this.$iframe = undefined
    }
  }

  doesViewerExist () {
    return this.$iframe !== undefined
  }

}
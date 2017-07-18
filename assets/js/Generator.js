import $ from 'jquery'
import debounce from 'debounce'
import inViewport from 'in-viewport'
import Spinner from './Spinner'
import Refresh from './Refresh'

export default class {

  constructor (args) {
    this.$form = args.form
    this.$container = args.container
    this.viewer = args.viewer
    this.spinner = new Spinner()

    this.formUpdated = false

    this.endpoint = args.endpoint
  }

  init () {

    /* */
    this.$form.change(() => this.trackFormChanges())

    /* */
    const manualLoader = new Refresh()
    manualLoader.init(this.$container, () => {
      this.generatePreview()
      return false
    })

    /* */
    $(window).scroll(() => debounce(this.maybeReloadPreview(), 1000))
  }

  isContainerinViewpoint () {
    return inViewport(this.$container[0])
  }

  maybeReloadPreview () {
    if (!this.viewer.doesViewerExist() || ( this.formUpdated && this.isContainerinViewpoint() )) {
      this.generatePreview()
    }
  }

  trackFormChanges () {
    this.formUpdated = true
  }

  async generatePreview () {
    /* Only reload if our container isn't currently hidden, or an update isn't already in progress */
    if (this.$container.is(':visible') && !this.updateInProgress) {
      this.updateInProgress = true;
      this.viewer.removeIframe()
      this.$container.addClass('gfpdf-loading')
      this.spinner.addSpinner(this.$container)

      let response = await this.callEndpoint()

      if (response.error) {
        console.error(response.error)
        return
      }

      this.displayPreview(response.id)
    }
  }

  displayPreview (id) {
    let $iframe = this.viewer.generateIframe(id);
    $iframe.on('load', () => {
      this.updateInProgress = false
      $iframe.show()
      this.spinner.removeSpinner()
      this.$container.removeClass('gfpdf-loading')
      this.formUpdated = false
    })

    this.$container.append($iframe)
  }

  callEndpoint () {
    return $.ajax({
      url: this.endpoint,
      method: "POST",
      data: this.$form.serialize(),
    })
  }
}
import $ from 'jquery'
import debounce from 'debounce'
import inViewport from 'in-viewport'
import Spinner from './Spinner'
import Refresh from './Refresh'

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       0.1
 */

/**
 * PDF Preview Generator class
 *
 * @since 0.1
 */
export default class {

  /**
   * @param args
   *            .form jQuery form object
   *            .container jQuery PDF Preview object
   *            .viewer object The Viewer.js initialised class
   *            .endpoint string The PDF generator REST API endpoint
   *
   * @since 0.1
   */
  constructor (args) {
    this.$form = args.form
    this.$container = args.container
    this.viewer = args.viewer
    this.spinner = new Spinner()

    this.formUpdated = false
    this.updateInProgress = false

    this.endpoint = args.endpoint
  }

  /**
   * Initialise our class
   *
   * @since 0.1
   */
  init () {

    /* Add listener to track any form change events (events bubble up the DOM) */
    this.$form.change(() => this.trackFormChanges())

    /* Register our manual PDF previewer loader */
    const manualLoader = new Refresh()
    manualLoader.add(this.$container, () => {
      this.generatePreview()
      return false
    })

    /* Add listener to the scroll event to trigger a reload */
    $(window).scroll(() => debounce(this.maybeReloadPreview(), 1000))

    /* Trigger the viewer if there is no scrollbar */
    if( window.innerWidth <= document.documentElement.clientWidth ) {
      this.maybeReloadPreview()
    }
  }

  /**
   * Check if the PDF Preview container is in the browser viewpoint
   *
   * @returns boolean
   *
   * @since 0.1
   */
  isContainerInViewpoint () {
    return inViewport(this.$container[0])
  }

  /**
   * Determine if the PDF preview should be generated
   *
   * @since 0.1
   */
  maybeReloadPreview () {

    /*
     * If the viewer hasn't already been initialised, OR
     * the form has been updated AND the previewer container is in the browser viewpoint
     * then we'll generate a new preview
     */
    if (!this.viewer.doesViewerExist() || (window['gf_submitting_' + this.$form.data('fid')] != true && this.formUpdated && this.isContainerInViewpoint())) {
      this.generatePreview()
    }
  }

  /**
   * Track the form updates
   *
   * @since 0.1
   */
  trackFormChanges () {
    this.formUpdated = true
  }

  /**
   * Does our REST API call to generate the PDF Preview
   *
   * @returns void
   *
   * @since 0.1
   */
  async generatePreview () {

    /*
     * Only reload the PDF if our container isn't currently hidden AND this function isn't already in progress
     */
    if (this.$container.is(':visible') && !this.updateInProgress) {
      /* Remove old PDF Preview */
      this.viewer.remove()

      /* Setup our loading environment */
      this.updateInProgress = true
      this.$container.addClass('gfpdf-loading')
      this.spinner.add(this.$container)

      /* Call our endpoint and catch any promise-related errors that might occur */
      try {
        var response = await this.callEndpoint()
      } catch (error) {
        response = {
          error: 'PDF Generation Error'
        }
      }

      /* Display error to end user */
      if (response.error) {
        this.handlePdfDisplayError(response.error)
        return
      }

      /* Load our newly generated PDF */
      this.displayPreview(response.id)
    }
  }

  /**
   * Display an error to the end user when there was a problem generating the PDF
   *
   * @param error
   *
   * @since 0.1
   */
  handlePdfDisplayError (error) {

    /* Log the error to the browser console */
    console.error(error)

    /* Display our friendly error */
    this.spinner.showLoadingError()

    /* Add a manual loader below the error so the user can try again */
    const manualLoader = new Refresh()
    manualLoader.add(this.spinner.$spinner, () => {
      this.updateInProgress = false
      this.generatePreview()
      return false
    }, 'white')
  }

  /**
   * Add our PDF Preview to the DOM
   *
   * @param id
   *
   * @since 0.1
   */
  displayPreview (id) {
    let $iframe = this.viewer.create(id)

    /* When the iFrame finishes loading we'll remove the AJAX loading environment */
    $iframe.on('load', () => {
      this.updateInProgress = false
      $iframe.show()
      this.spinner.remove()
      this.$container.removeClass('gfpdf-loading')
      this.formUpdated = false
    })

    /* Add iFrame to the DOM */
    this.$container.append($iframe)
  }

  /**
   * Make our REST API call
   *
   * @since 0.1
   */
  callEndpoint () {
    if (typeof(tinyMCE) != 'undefined') {
      tinyMCE.triggerSave()
    }

    return $.ajax({
      url: this.endpoint,
      method: "POST",
      data: this.$form.serializeArray().filter((item) => {
        return $.inArray(item.name, ['_wpnonce', 'add-to-cart']) === -1
      })
    })
  }
}
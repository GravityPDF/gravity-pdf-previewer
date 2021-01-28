import debounce from 'debounce'
import Spinner from './Spinner'
import Refresh from './Refresh'
import { isInViewport } from './utilities/isInViewport'
import { serializeFormData } from './utilities/serializeFormData'

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
   * .form form object
   * .formId form id integer
   * .container element wrapper of PDF Previewer
   * .viewer object The Viewer.js initialised class
   * .endpoint string The PDF generator REST API endpoint
   * .formUpdated boolean
   * .failedRequest boolean
   * .spinner object initialised spinner
   * .controller object Initialize fetch abort controller
   *
   * @since 0.1
   */
  constructor (args) {
    this.form = args.form
    this.formId = args.formId
    this.container = args.container
    this.viewer = args.viewer
    this.endpoint = args.endpoint
    this.formUpdated = false
    this.failedRequest = false
    this.spinner = new Spinner()
    this.controller = new window.AbortController()
  }

  /**
   * Initialise our class
   *
   * @since 0.1
   */
  init () {
    /* Add listener to track any form change events (events bubble up the DOM) */
    jQuery(this.form).on('change input', () => this.trackFormChanges())

    /* Register our manual PDF previewer loader */
    const manualLoader = new Refresh()
    manualLoader.add(this.container, () => this.generatePreview())

    /* Add listener to the onload and scroll event to trigger a reload */
    window.addEventListener('scroll', () => debounce(this.maybeReloadPreview(), 1000))

    /* Load current viewer right away if the parent container isn't hidden */
    window.addEventListener('load', function() {
      if (this.container.parentElement.style.display !== 'none') {
        this.generatePreview()
      }
    }.bind(this))
  }

  /**
   * Determine if the PDF preview should be generated
   *
   * @since 0.1
   */
  maybeReloadPreview () {
    /*
     * If the form has been updated and not in submitting process then we'll generate a new preview
     */
    if (window['gf_submitting_' + this.formId] !== true && this.formUpdated && isInViewport(this.container)) {
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
     * Only reload if the previous request is resolved
     */
    if (!this.failedRequest) {
      /* Take only the new request and abort the previous request */
      this.controller.abort()
      this.controller = new window.AbortController()

      /* Remove old PDF Preview */
      this.viewer.remove()

      /* Setup our loading environment */
      this.formUpdated = false
      this.container.classList.add('gfpdf-loading')
      this.spinner.add(this.container)

      /* Call our endpoint and catch any promise-related errors that might occur */
      let response

      try {
        response = await this.callEndpoint()
      } catch (error) {
        response = { error: error.message ? undefined : 'PDF Generation Error' }
      }

      /* Display error to end user */
      if (response.error) {
        return this.handlePdfDisplayError(response.error)
      }

      /* Load our newly generated PDF */
      this.displayPreview(response.id)
    }
  }

  /**
   * Display an error to the end user when there was a problem generating the PDF
   *
   * @param error: string
   *
   * @since 0.1
   */
  handlePdfDisplayError (error) {
    this.failedRequest = true

    /* Log the error to the browser console */
    console.error(error)

    /* Display our friendly error */
    this.spinner.showLoadingError()

    /* Add a manual loader below the error so the user can try again */
    const manualLoader = new Refresh()
    manualLoader.add(this.spinner.spinner, () => {
      this.failedRequest = false
      this.generatePreview()
      this.removeSpinner()
    }, 'white')
  }

  /**
   * Add our PDF Preview to the DOM
   *
   * @param id: string | undefined
   *
   * @since 0.1
   */
  displayPreview (id) {
    /* Remove spinner for cancelled requests */
    if (id === undefined) {
      return this.removeSpinner()
    }

    /* Ensure removal of old spinner from previous request */
    this.removeSpinner()

    const iframe = this.viewer.create(id)

    /* When the iFrame finishes loading we'll remove the AJAX loading environment */
    iframe.addEventListener('load', () => {
      iframe.style.display = 'inline'
      this.spinner.remove()
      this.container.classList.remove('gfpdf-loading')
    })

    /* Add iFrame to the DOM */
    this.container.appendChild(iframe)
  }

  /**
   * Make our REST API call
   *
   * @returns { result: Object }
   *
   * @since 0.1
   */
  async callEndpoint () {
    if (typeof (tinyMCE) !== 'undefined') {
      tinyMCE.triggerSave()
    }

    /* Actual API call */
    const response = await window.fetch(this.endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      signal: this.controller.signal,
      body: serializeFormData(this.form)
    })

    /* API response */
    const result = await response.json()

    return result
  }

  /**
   * Remove Spinner
   *
   * @since 1.3
   */
  removeSpinner () {
    this.container.querySelector('.gpdf-spinner').remove()
  }
}

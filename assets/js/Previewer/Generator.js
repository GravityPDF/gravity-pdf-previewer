import $ from 'jquery'
import debounce from 'debounce'
import inViewport from 'in-viewport'
import Spinner from './Spinner'
import Refresh from './Refresh'

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2017, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/*
    This file is part of Gravity PDF Previewer.

    Copyright (C) 2017, Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published
    by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
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
    manualLoader.init(this.$container, () => {
      this.generatePreview()
      return false
    })

    /* Add listener to the scroll event to trigger a reload */
    $(window).scroll(() => debounce(this.maybeReloadPreview(), 1000))
  }

  /**
   * Check if the PDF Preview container is in the browser viewpoint
   *
   * @returns boolean
   *
   * @since 0.1
   */
  isContainerinViewpoint () {
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
    if (!this.viewer.doesViewerExist() || ( this.formUpdated && this.isContainerinViewpoint() )) {
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
      this.viewer.removeIframe()

      /* Setup our loading environment */
      this.updateInProgress = true
      this.$container.addClass('gfpdf-loading')
      this.spinner.addSpinner(this.$container)

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
    manualLoader.init(this.spinner.$spinner, () => {
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
    let $iframe = this.viewer.generateIframe(id)

    /* When the iFrame finishes loading we'll remove the AJAX loading environment */
    $iframe.on('load', () => {
      this.updateInProgress = false
      $iframe.show()
      this.spinner.removeSpinner()
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
    return $.ajax({
      url: this.endpoint,
      method: "POST",
      data: this.$form.serialize(),
    })
  }
}
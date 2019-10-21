import debounce from 'debounce'
import inViewport from 'in-viewport'
import Spinner from './Spinner'
import Refresh from './Refresh'

/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/*
    This file is part of Gravity PDF Previewer.

    Copyright (C) 2018, Blue Liquid Designs

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
   * .form form object
   * .container element wrapper of PDF Previewer
   * .viewer object The Viewer.js initialised class
   * .endpoint string The PDF generator REST API endpoint
   * .spinner object initialised spinner
   * .formUpdated boolean
   * .updateInProgress boolean
   *
   * @since 0.1
   */
  constructor (args) {
    this.form = args.form
    this.container = args.container
    this.viewer = args.viewer
    this.endpoint = args.endpoint
    this.spinner = new Spinner()
    this.formUpdated = false
    this.updateInProgress = false
  }

  /**
   * Initialise our class
   *
   * @since 0.1
   */
  init () {
    /* Add listener to track any form change events (events bubble up the DOM) */
    this.form.addEventListener('change', () => {
      this.trackFormChanges()
      return false
    })

    /* Register our manual PDF previewer loader */
    const manualLoader = new Refresh()
    manualLoader.add(this.container, () => {
      this.generatePreview()
      return false
    })

    /* Add listener to the scroll event to trigger a reload */
    window.addEventListener('scroll', () => debounce(this.maybeReloadPreview(), 1000))

    /* Trigger the viewer if there is no scrollbar */
    if (window.innerWidth <= document.documentElement.clientWidth) {
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
    return inViewport(this.container)
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
    if (!this.viewer.doesViewerExist() || this.formUpdated && this.isContainerInViewpoint()) {
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
    if (this.container !== null && !this.updateInProgress) {
      /* Remove old PDF Preview */
      this.viewer.remove()

      /* Setup our loading environment */
      this.updateInProgress = true
      this.container.classList.add('gfpdf-loading')
      this.spinner.add(this.container)

      /* Call our endpoint and catch any promise-related errors that might occur */
      let response

      try {
        response = await this.callEndpoint()
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
    manualLoader.add(this.spinner.spinner, () => {
      this.updateInProgress = false
      this.generatePreview()
      return false
    }, 'white')
  }

  /**
   * Add our PDF Preview to the DOM
   *
   * @param id*-+
   *
   * @since 0.1
   */
  displayPreview (id) {
    let iframe = this.viewer.create(id)

    /* When the iFrame finishes loading we'll remove the AJAX loading environment */
    iframe.addEventListener('load', () => {
      this.updateInProgress = false
      iframe.style.display = 'inline'
      this.spinner.remove()
      this.container.classList.remove('gfpdf-loading')
      this.formUpdated = false
    })

    /* Add iFrame to the DOM */
    this.container.appendChild(iframe)
  }

  /**
   * Make our REST API call
   *
   * @returns Object
   *
   * @since 0.1
   */
  async callEndpoint () {
    if (typeof (tinyMCE) != 'undefined') {
      tinyMCE.triggerSave()
    }

    let keypairs = []

    /* Query form */
    let form = document.querySelector('form')

    /* Creates a list of key-value pairs (name=value) and pushed into the empty array */
    for ( let i = 0; i < form.elements.length; i++ ) {
      let e = form.elements[i]
      keypairs.push(e.name + "=" + e.value)
    }

    /* Joined together key-value pairs using "&" as a delimiter */
    let queryString = keypairs.join('&')

    /* Actual API call */
    const response = await fetch(this.endpoint, {
      method: 'POST',
      headers: {
        'Content-Type':'application/x-www-form-urlencoded'
      },
      body: queryString
    })

    /* API response */
    const result = await response.json()

    return result
  }
}

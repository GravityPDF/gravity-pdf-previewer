import $ from 'jquery'

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
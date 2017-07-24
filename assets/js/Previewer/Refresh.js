import $ from 'jquery'

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
 * Adds a refresh icon to the DOM
 *
 * @since 0.1
 */
export default class {

  /**
   * @param jQuery $container The container the icon is appended to
   * @param func callback The function to run when the icon is clicked
   * @param string type The colour of the icon. Black and White are supported. Default to black.
   *
   * @since 0.1
   */
  add ($container, callback, type = 'black') {

    /* Get the correct icon */
    const refreshIcon = (type === 'white') ? require('svg-url-loader?noquotes!../../images/refresh-white.svg') : require('svg-url-loader?noquotes!../../images/refresh.svg')

    /* Create a wrapper */
    this.$refresh = $('<div>')
      .addClass('gpdf-manually-load-preview')

    /* Create an img DOM element for our icon */
    let $refresh = $('<img>')
      .attr('src', refreshIcon)
      .height(17)

    /* Add our link and icon to our wrapper */
    this.$refresh.append(
      $('<a>')
        .attr('href', '#')
        .attr('title', PdfPreviewerConstants.refreshTitle)
        .click(callback)
        .append($refresh)
    )

    /* Append the wrapper to the $container */
    $container.append(this.$refresh)
  }

  remove () {
    if (this.$refresh) {
      this.$refresh.remove()
    }
  }
}
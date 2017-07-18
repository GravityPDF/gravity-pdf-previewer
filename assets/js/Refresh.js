import $ from 'jquery'
const refreshIcon = require('svg-url-loader?noquotes!../images/refresh.svg');

export default class {

  init ($container, callback) {
    this.$refresh = $('<div>')
      .addClass('gpdf-manually-load-preview')

    let $refresh = $('<img>')
      .attr('src', refreshIcon)
      .height(17)

    this.$refresh.append(
      $('<a>')
        .attr('href', '#')
        .attr('title', 'Refresh PDF') /* @TODO */
        .click(callback)
        .append($refresh)
    )

    $container.append(this.$refresh)
  }
}
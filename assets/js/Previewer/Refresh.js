import $ from 'jquery'

export default class {

  init ($container, callback, type) {
    const refreshIcon = (type === 'white') ? require('svg-url-loader?noquotes!../../images/refresh-white.svg') : require('svg-url-loader?noquotes!../../images/refresh.svg')

    this.$refresh = $('<div>')
      .addClass('gpdf-manually-load-preview')

    let $refresh = $('<img>')
      .attr('src', refreshIcon)
      .height(17)

    this.$refresh.append(
      $('<a>')
        .attr('href', '#')
        .attr('title', PdfPreviewerConstants.refreshTitle)
        .click(callback)
        .append($refresh)
    )

    $container.append(this.$refresh)
  }
}
import $ from 'jquery'

// setup global defaults that our tests expect is present
window.PdfPreviewerConstants = {
  loadingMessage: 'Loading',
  errorMessage: 'loading error',
  refreshTitle: 'refresh_title'
}

// setup global before and after code
beforeEach(function () {
  $('body')
    .append('<div id="karma-test-container">')
})

afterEach(function () {
  $('#karma-test-container').remove()
})

// require all modules ending in ".test.js" from the
// current directory and all subdirectories
var testsContext = require.context(".", true, /.+\.test\.js?$/)
testsContext.keys().forEach(testsContext)
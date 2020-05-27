// setup global defaults that our tests expect is present
window.PdfPreviewerConstants = {
  loadingMessage: 'Loading',
  errorMessage: 'loading error',
  refreshTitle: 'refresh_title'
}

// setup global before and after code
beforeEach(function () {
  let body = document.querySelector('body')
  let container = document.createElement('div')
  container.setAttribute('id', 'karma-test-container')
  body.appendChild(container)
})

afterEach(function () {
  document.querySelector('#karma-test-container').remove()
})

// require all modules ending in ".test.js" from the
// current directory and all subdirectories
let testsContext = require.context('.', true, /.+\.test\.js?$/)
testsContext.keys().forEach(testsContext)

import Generator from '../../../../assets/js/Previewer/Generator'
import Viewer from '../../../../assets/js/Previewer/Viewer'
import 'abortcontroller-polyfill/dist/abortcontroller-polyfill-only'

describe('Generator Class', () => {

  let container, form, viewer, generator

  beforeEach(function () {
    container = document.querySelector('#karma-test-container')
    form = document.createElement('form')
    form.appendChild(document.createElement('input'))

    viewer = new Viewer({
      viewerHeight: '600',
      viewer: 'http://localhost/'
    })

    generator = new Generator({
      form: form,
      container: container,
      viewer: viewer,
      endpoint: 'endpointUrl'
    })

    /* Mock Api call */
    generator.callEndpoint = () => {
      return new Promise((resolve) => {
        resolve({ token: '12345bcd' })
      })
    }
  })

  it('Track form changes', () => {
    expect(generator.formUpdated).to.be.false

    generator.trackFormChanges()
    expect(generator.formUpdated).to.be.true
  })

  it('Test endpoint stub', (done) => {
    generator.callEndpoint().then((response) => {
      expect(response.token).to.equal('12345bcd')
      done()
    })
  })

  it('Test display preview', () => {
    generator.spinner.add(generator.container)
    generator.displayPreview('12345abc')
    expect(container.querySelector('iframe')).to.exist
  })

  it('Test PDF Display Error', () => {
    let errorLog = console.error
    console.error = () => {}
    generator.spinner.add(generator.container)
    generator.handlePdfDisplayError('My error')

    expect(container.querySelector('.gpdf-spinner').textContent).to.equal('loading error')
    expect(container.querySelector('.gpdf-spinner .gpdf-manually-load-preview a img')).to.exist

    console.error = errorLog
  })

  it('Test PDF Preview Generator', (done) => {
    generator.generatePreview().then(() => {
      expect(container.querySelector('iframe')).to.exist
      done()
    })
  })
})

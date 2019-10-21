import Generator from '../../../../assets/js/Previewer/Generator'
import Viewer from '../../../../assets/js/Previewer/Viewer'

describe('Generator Class', () => {

  let container, form, viewer, generator

  beforeEach(function () {
    container = document.querySelector('#karma-test-container')
    form = document.createElement('form')
    form.appendChild(document.createElement('input'))

    viewer = new Viewer({
      viewerHeight: '600',
      viewer: 'http://localhost/',
      documentUrl: 'documentUrl'
    })

    generator = new Generator({
      form: form,
      container: container,
      viewer: viewer,
      endpoint: 'endpointUrl'
    })

    /* Mock Api call */
    generator.callEndpoint = (response) => {
      return new Promise((resolve, reject) => {
        resolve({
          id: '12345abc'
        })
      })
    }
  })

  it('Is container in view', () => {
    expect(generator.isContainerInViewpoint()).to.be.false
  })

  it('Track form changes', () => {
    expect(generator.formUpdated).to.be.false

    generator.trackFormChanges()
    expect(generator.formUpdated).to.be.true
  })

  it('Test endpoint stub', (done) => {
    generator.callEndpoint().then((response) => {
      expect(response.id).to.equal('12345abc')
      done()
    })
  })

  it('Test display preview', () => {
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

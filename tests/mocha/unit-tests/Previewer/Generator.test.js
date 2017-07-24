import $ from 'jquery'
import Generator from '../../../../assets/js/Previewer/Generator'
import Viewer from '../../../../assets/js/Previewer/Viewer'

describe('Generator Class', () => {

  $.ajax = (response) => {
    return new Promise((resolve, reject) => {
      resolve({
        id: '12345abc'
      })
    })
  }

  var generator, $container

  beforeEach(function () {
    $container = $('#karma-test-container')
    let $form = $('<form>')
    $form.append($('<input>'))

    let viewer = new Viewer({
      viewerHeight: '600',
      viewer: 'viewerUrl',
      documentUrl: 'documentUrl'
    })

    generator = new Generator({
      form: $form,
      container: $container,
      viewer: viewer,
      endpoint: 'endpointUrl'
    })
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
    expect($container.find('iframe').length).to.equal(1)
  })

  it('Test PDF Display Error', () => {

    let errorLog = console.error
    console.error = () => {}
    generator.spinner.add(generator.$container)
    generator.handlePdfDisplayError('My error')

    expect($container.find('.gpdf-spinner').text()).to.equal('loading error')
    expect($container.find('.gpdf-spinner .gpdf-manually-load-preview a img').length).to.equal(1)

    console.error = errorLog
  })

  it('Test PDF Preview Generator', (done) => {
    generator.generatePreview().then((response) => {
      expect($container.find('iframe').length).to.equal(1)
      done()
    })
  })

})
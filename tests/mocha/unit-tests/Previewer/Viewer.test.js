import $ from 'jquery'
import Viewer from '../../../../assets/js/Previewer/Viewer'

describe('Viewer Class', () => {

  var viewer, $container

  beforeEach(function () {
    $container = $('#karma-test-container')
    viewer = new Viewer({
      viewerHeight: 500,
      viewer: 'viewerUrl',
      documentUrl: 'documentUrl',
    })
  })

  afterEach(function () {
    viewer.remove()
  })

  it('Test create viewer', () => {

    let $iframe = viewer.create('testID')

    expect($iframe.attr('src')).to.equal('viewerUrl?file=documentUrltestID')
    expect($iframe.height()).to.equal(500)
  })

  it('Test remove viewer', () => {
    let $iframe = viewer.create('testID')

    $container.append($iframe)
    expect($container.find('iframe').length).to.equal(1)

    $iframe.remove()
    expect($container.find('iframe').length).to.equal(0)
  })

  it('Test viewer exists', () => {
    let $iframe = viewer.create('testID')

    expect(viewer.doesViewerExist()).to.be.true

    viewer.remove()
    expect(viewer.doesViewerExist()).to.be.false
  })

})
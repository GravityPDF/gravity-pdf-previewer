import Viewer from '../../../../assets/js/Previewer/Viewer'

describe('Viewer Class', () => {

  let viewer, container

  beforeEach(function () {
    container = document.querySelector('#karma-test-container')
    viewer = new Viewer({
      viewerHeight: 500,
      viewer: 'http://localhost/',
      documentUrl: 'documentUrl',
    })
  })

  afterEach(function () {
    viewer.remove()
  })

  it('Test create viewer', () => {
    let iframe = viewer.create('testID')

    expect(iframe.getAttribute('src')).to.equal('http://localhost/?file=documentUrltestID')
    expect(iframe.getAttribute('height')).to.equal('500')
  })

  it('Test remove viewer', () => {
    let iframe = viewer.create('testID')

    container.appendChild(iframe)
    expect(container.querySelector('iframe')).to.exist

    iframe.remove()
    expect(container.querySelector('iframe')).to.be.null
  })

  it('Test viewer exists', () => {
    viewer.create('testID')
    expect(viewer.doesViewerExist()).to.be.true

    viewer.remove()
    expect(viewer.doesViewerExist()).to.be.false
  })
})

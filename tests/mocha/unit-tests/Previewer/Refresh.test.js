import Refresh from '../../../../assets/js/Previewer/Refresh'

describe('Refresh Class', () => {

  let refresh, container

  beforeEach(function () {
    container = document.querySelector('#karma-test-container')
    refresh = new Refresh()
  })

  afterEach(function () {
    refresh.remove()
  })

  it('Test add refresh', () => {
    refresh.add(container, () => {})

    expect(refresh.refresh.getAttribute('class')).to.equal('gpdf-manually-load-preview')
    expect(container.querySelector('.gpdf-manually-load-preview a').getAttribute('title')).to.equal('refresh_title')
    expect(container.querySelector('.gpdf-manually-load-preview a img')).to.exist
  })

  it('Test callback', (done) => {
    refresh.add(container, () => {
      done()
    })

    refresh.refresh.querySelector('a').click()
  })

  it('Test remove', () => {
    refresh.add(container, () => {})
    expect(container.querySelector('.gpdf-manually-load-preview a img')).to.exist

    refresh.remove()
    expect(container.querySelector('.gpdf-manually-load-preview a img')).to.be.null
  })

  it('Test loading white icon', () => {
    refresh.add(container, () => {}, 'white')
  })
})

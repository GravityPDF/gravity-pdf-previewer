import $ from 'jquery'
import Refresh from '../../../../assets/js/Previewer/Refresh'

describe('Refresh Class', () => {

  var refresh, $container

  beforeEach(function () {
    $container = $('#karma-test-container')
    refresh = new Refresh()
  })

  afterEach(function () {
    refresh.remove()
  })

  it('Test add refresh', () => {
    refresh.add($container, () => {})

    expect(refresh.$refresh.attr('class')).to.equal('gpdf-manually-load-preview')
    expect($container.find('.gpdf-manually-load-preview a').attr('title')).to.equal('refresh_title')
    expect($container.find('.gpdf-manually-load-preview a img').length).to.equal(1)
  })

  it('Test callback', (done) => {
    refresh.add($container, () => {
      done()
    })

    refresh.$refresh.find('a').click()
  })

  it('Test remove', () => {
    refresh.add($container, () => {})
    expect($container.find('.gpdf-manually-load-preview a img').length).to.equal(1)

    refresh.remove()
    expect($container.find('.gpdf-manually-load-preview a img').length).to.equal(0)
  })

  it('Test loading white icon', () => {
    refresh.add($container, () => {}, 'white')
  })
})
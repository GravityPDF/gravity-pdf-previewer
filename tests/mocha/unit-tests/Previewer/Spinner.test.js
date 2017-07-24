import $ from 'jquery'
import Spinner from '../../../../assets/js/Previewer/Spinner'

describe('Spinner Class', () => {

  var spinner, $container

  beforeEach(function () {
    $container = $('#karma-test-container')
    spinner = new Spinner()
  })

  afterEach(function () {
    spinner.remove()
  })

  it('Test add spinner', () => {
    spinner.add($container)

    expect(spinner.$spinner.attr('class')).to.equal('gpdf-spinner')
    expect($container.find('.gpdf-spinner').length).to.equal(1)
    expect($container.find('.gpdf-spinner img').length).to.equal(1)
    expect($container.find('.gpdf-spinner').text()).to.equal('Loading')

    spinner.remove()
    expect($container.find('.gpdf-spinner').length).to.equal(0)
  })

  it('Test Loading Error', () => {
    spinner.add($container)
    expect($container.find('.gpdf-spinner').text()).to.not.equal('loading error')

    spinner.showLoadingError()
    expect($container.find('.gpdf-spinner').text()).to.equal('loading error')
  })
})
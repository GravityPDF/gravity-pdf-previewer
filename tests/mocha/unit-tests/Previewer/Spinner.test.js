import Spinner from '../../../../assets/js/Previewer/Spinner'

describe('Spinner Class', () => {

  let spinner, container

  beforeEach(function () {
    container = document.querySelector('#karma-test-container')
    spinner = new Spinner()
  })

  afterEach(function () {
    spinner.remove()
  })

  it('Test add spinner', () => {
    spinner.add(container)

    expect(spinner.spinner.getAttribute('class')).to.equal('gpdf-spinner')
    expect(container.querySelector('.gpdf-spinner')).to.exist
    expect(container.querySelector('.gpdf-spinner img')).to.exist
    expect(container.querySelector('.gpdf-spinner').innerText).to.equal('Loading')

    spinner.remove()
    expect(container.querySelector('.gpdf-spinner')).to.be.null
  })

  it('Test Loading Error', () => {
    spinner.add(container)
    expect(container.querySelector('.gpdf-spinner').innerText).to.not.equal('loading error')

    spinner.showLoadingError()
    expect(container.querySelector('.gpdf-spinner').innerText).to.equal('loading error')
  })
})

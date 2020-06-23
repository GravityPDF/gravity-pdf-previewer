/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

/**
 * Check if the element is in the viewport
 *
 * @param elem: Refresh icon container
 *
 * @returns { boolean }
 *
 * @since 2.0
 */
export const isInViewport = elem => {
  if (!elem) {
    return false
  }

  let top = elem.offsetTop
  let left = elem.offsetLeft
  const width = elem.offsetWidth
  const height = elem.offsetHeight

  while (elem.offsetParent) {
    elem = elem.offsetParent
    top += elem.offsetTop
    left += elem.offsetLeft
  }

  return (
    top < (window.pageYOffset + window.innerHeight) &&
    left < (window.pageXOffset + window.innerWidth) &&
    (top + height) > window.pageYOffset &&
    (left + width) > window.pageXOffset
  )
}

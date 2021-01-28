/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.3
 */

/**
 * Query PDF previewer wrapper container
 *
 * @param multipleFormPages: Multiple gravity form pages element
 * @param form: Current gravity form page element
 *
 * @returns { HTML element }
 *
 * @since 1.3
 */
export const previewerWrapper = (multipleFormPages, form) => {
  if (multipleFormPages) {
    return multipleFormPages.querySelectorAll('.gpdf-previewer-wrapper')
  }

  return form.querySelectorAll('.gpdf-previewer-wrapper')
}

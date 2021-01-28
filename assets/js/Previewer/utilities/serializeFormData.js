/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2021, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.3
 */

/**
 * Serialize all form data into a query string
 *
 * @param form
 *
 * @returns { result: string }
 *
 * @since 1.3
 */
export const serializeFormData = form => {
  /* Setup our serialized data */
  const serialized = []

  /* Loop through each field in the */
  for (let i = 0; i < form.elements.length; i++) {
    const field = form.elements[i]

    /* Don't serialize fields without a name, submits, buttons, file and reset inputs, and disabled fields */
    if (
      !field.name ||
      field.name === '_wpnonce' ||
      field.name === 'add-to-cart' ||
      field.disabled ||
      field.type === 'file' ||
      field.type === 'reset' ||
      field.type === 'submit' ||
      field.type === 'button'
    ) {
      continue
    }

    /* If a multi-select, get all selections */
    if (field.type === 'select-multiple') {
      for (let n = 0; n < field.options.length; n++) {
        serialized.push(encodeURIComponent(field.name) + '=' + encodeURIComponent(field.options[n].value))
      }
    }

    /* Convert field data to a query string */
    if ((field.type !== 'checkbox' && field.type !== 'radio') || field.checked) {
      serialized.push(encodeURIComponent(field.name) + '=' + encodeURIComponent(field.value))
    }
  }

  /* Joined together the serialized data using "&" as a delimiter */
  const result = serialized.join('&')

  return result
}

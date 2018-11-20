=== Gravity PDF Previewer ===

== Frequently Asked Questions ==

= How do I receive support? =

User's with a valid, active license key can receive support for this plugin by filling out the form at [GravityPDF.com](https://gravitypdf.com/support/).

== Changelog ==

= Version 1.2.1, November 20, 2018 =

* Bug: Ensure Rich Text Field content displayed correctly in Previewer

= Version 1.2.0, October 8, 2018 =

* Feature: Add support for the WooCommerce Gravity Forms Product add-on
* Bug: Ensure the Preview is automatically loaded when there's no scroll bar on the page

= Version 1.1.1, June 29, 2018 =

* Bug: Prevent PHP error when developers tap into the `gform_pre_render` filter
* Bug: Disable PDF security preventing copying / printing of the PDF when the PDF Previewer download feature is enabled

= Version 1.1.0, February 14, 2018 =

* Feature: Add Gravity Flow v2.0+ User Input Step support.
* Feature: Add setting to allow end-user to download generated PDF (defaults to off)
* Feature: Add full support for uploaded files in GravityView
* Feature: Define `DOING_PDF_PREVIEWER` PHP constant when generating PDFs for Previewer.
* Bug: Prevent Previewer showing up in Core / Universal templates when `Show Empty Fields` option enabled.

= Version 1.0.2, November 9, 2017 =

* Bug: Fix problem where the PDF watermark and custom height settings were ignored for new Previewer fields

= Version 1.0.1, October 30, 2017 =

* Feature: Trigger `gform_pre_submission` action before temporary entry is created to allow raw $_POST data to be modified
* Bug: Prevent any miscellaneous output when generating the preview PDF
* Bug: Clear temporary entry meta data to prevent product information being cached
* Bug: Add `!important` statements to our loading spinner CSS to prevent display issues caused by themes
* Bug: Mark our Previewer field as `read only` in Gravity Forms to prevent is showing up in conditional logic, merge tags or the entry details page

= Version 1.0, August 17, 2017 =

* Feature: Add French, Spanish and German translations
* Bug: Fix double-encoding issue in the Preview PDF field strings
* Bug: Adjust pre-loading checks so they correctly display when there\'s a problem

= Version 0.2, August 11, 2017 =

* Bug: Default to first PDF for the preview if none selected
* Bug: Fix Watermark double toggle problem
* Feature: Add more robust logging support
* Dev: Upgrade bootstrap to utilise Gravity PDF 4.3 Add-on Code
* Dev: Remove unnecessary files from the plugin

= Version 0.1, August 1, 2017 =

* Initial Release

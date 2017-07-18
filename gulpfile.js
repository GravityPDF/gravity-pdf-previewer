var gulp = require('gulp'),
  wpPot = require('gulp-wp-pot')

/* Generate the latest language files */
gulp.task('language', function () {
  return gulp.src(['src/**/*.php', '*.php'])
    .pipe(wpPot({
      domain: 'gravity-pdf-previewer',
      package: 'Gravity PDF Previewer'
    }))
    .pipe(gulp.dest('languages/gravity-pdf-previewer.pot'))
})

gulp.task('default', ['language'])
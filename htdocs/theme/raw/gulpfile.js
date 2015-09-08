// Include gulp
var gulp = require('gulp');

// Polyfill so we don't need >= node 0.12
require('es6-promise').polyfill();

// Include plugins
var sass = require('gulp-sass');
var minifyCSS = require('gulp-minify-css');
var autoprefixer = require('gulp-autoprefixer');
var bless = require('gulp-bless');

// Turn sass into css, prefix, minify and bless
gulp.task('sass', function () {
  return gulp.src('sass/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer({
      browsers: ['last 4 version'],
      cascade: false
    }))
    .pipe(minifyCSS())
    .pipe(bless())
    .pipe(gulp.dest('style/'));
});

// Watch files for changes
gulp.task('watch', function() {
    gulp.watch('sass/**/*.scss', ['sass']);
});

// Default task (recompile on init before watching)
gulp.task('default', ['sass', 'watch']);

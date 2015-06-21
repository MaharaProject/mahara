// Include gulp
var gulp = require('gulp');

// Include Our Plugins
var sass = require('gulp-sass');
var postcss = require('gulp-postcss');
var path = require('path');
var minifyCSS = require('gulp-minify-css');
var autoprefixer = require('autoprefixer-core');


// Turn sass into css
gulp.task('sass', function () {
  return gulp.src('sass/**/*.scss')
    .pipe(sass({
      paths: [ path.join(__dirname, 'sass', 'includes') ]
    }))
    .on('error', function(err){
        console.log(err); // catch and log the error, don't kill the process
        this.emit('end');
    })
    .pipe(gulp.dest('style/'));
});

// Prefix and minify css files
// this will first run the 'sass' task above, then this one
gulp.task('css', ['sass'], function () {
  return gulp.src('style/*.css')
    .pipe(postcss([ autoprefixer({ browsers: ['last 4 version'] }) ]))
    .on('error', function(err){
      console.log(err); // catch and log the error, don't kill the process
      this.emit('end');
    })
    .pipe(minifyCSS())
    .pipe(gulp.dest('style/'));
});

// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('sass/**/*.scss', ['css']);
});

// Default Task
gulp.task('default', ['watch']);

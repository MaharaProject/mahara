// Include gulp
var gulp = require('gulp-help')(require('gulp'));

//Polyfill so we don't need >= node 0.12
require('es6-promise').polyfill();

// Include Our Plugins
var sass = require('gulp-sass');
var path = require('path');
var minifyCSS = require('gulp-minify-css');
var autoprefixer = require('gulp-autoprefixer');
var bless = require('gulp-bless');
var es = require('event-stream');
var globule = require('globule');
var argv = require('yargs').default('production', 'true').argv;
var gulpif = require('gulp-if');

// Locate all the themes (they're the directories with a "themeconfig.php" in them)
var themes = globule.find('htdocs/theme/*/themeconfig.php');
themes = themes.map(function(themepath){
    themepath = path.join(themepath, '..');
    return themepath;
});

// Turn sass into css
gulp.task('css', 'Compile SASS into CSS', function () {
    var tasks = themes.map(function(themepath){

        console.log("Compiling CSS for " + themepath);
        return gulp.src('sass/**/*.scss', {cwd: themepath})
            .pipe(gulpif(argv.production !== 'false', sass().on('error', sass.logError), sass({
                style: 'expanded',
                sourceComments: 'normal'
            }).on('error', sass.logError)))
            .pipe(autoprefixer({
              browsers: ['last 4 version'],
              cascade: false
            }))
            .pipe(gulpif(argv.production !== 'false', minifyCSS()))
            .pipe(gulpif(argv.production !== 'false', bless()))
            .pipe(gulp.dest('style/', {cwd: themepath}));
    });

    return es.concat.apply(null, tasks);
});

// Watch Files For Changes
gulp.task('watch', 'Watch style directories and auto-compile CSS', function() {
    gulp.watch('htdocs/theme/**/sass/**/*.scss', ['css']);
});

// Default Task (recompile on init before watching)
gulp.task('default', ['css', 'watch']);

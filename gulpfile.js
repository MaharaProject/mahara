// Include gulp
var gulp = require('gulp');

//Polyfill so we don't need >= node 0.12
require('es6-promise').polyfill();

// Include Our Plugins
const sass = require('gulp-sass')
sass.compiler = require('sass')
const Fiber = require('fibers')

var path = require('path');
var cleanCSS = require('gulp-clean-css');
var autoprefixer = require('gulp-autoprefixer');
var es = require('event-stream');
var globule = require('globule');
var argv = require('yargs').default('production', 'true').argv;
var gulpif = require('gulp-if');

// Locate all the themes (they're the directories with a "themeconfig.php" in them)
var themes = globule.find('htdocs/theme/*/themeconfig.php');
themes = themes.map(function (themepath) {
    themepath = path.join(themepath, '..');
    return themepath;
});

// Turn sass into css
async function css() {
    var tasks = themes.map(function (themepath) {

        console.log("Compiling CSS for " + themepath);
        return gulp.src('sass/**/*.scss', { cwd: themepath })
            .pipe(gulpif(argv.production !== 'false', sass({
                includePaths: ['./node_modules'],
                fiber: Fiber
              }).on('error', sass.logError), sass({
                style: 'expanded',
                sourceComments: 'normal'
            }).on('error', sass.logError)))
            .pipe(autoprefixer({
                browsers: ['last 4 version'],
                cascade: false
            }))
            .pipe(gulpif(argv.production !== 'false', cleanCSS()))
            .pipe(gulp.dest('style/', { cwd: themepath }));
    });

    return es.concat.apply(null, tasks);
};

function watch() {
    gulp.watch('htdocs/theme/*/sass/**/*.scss', gulp.series('css'));
}

gulp.task('css', css);
gulp.task('watch', watch);

gulp.task('default', gulp.series(css, watch));
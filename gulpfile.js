/**
 * Gulpfile
 * Migration from v4 to v5: https://github.com/dlmanning/gulp-sass/tree/master#migrating-to-version-5
 */

// Include gulp
const gulp = require('gulp');

//Polyfill so we don't need >= node 0.12
require('es6-promise').polyfill();

// Include Our Plugins
const sass = require('gulp-sass')(require('sass'));

const path = require('path');
const cleanCSS = require('gulp-clean-css');
const autoprefixer = require('gulp-autoprefixer');
const es = require('event-stream');
const globule = require('globule');
const argv = require('yargs').default('production', 'true').argv;
const gulpif = require('gulp-if');

// Locate all the themes (they're the directories with a "themeconfig.php" in them)
let themes = globule.find('htdocs/theme/*/themeconfig.php');
themes = themes.map(function (themepath) {
    themepath = path.join(themepath, '..');
    return themepath;
});

// Turn sass into css
async function css() {
    const tasks = themes.map(function (themepath) {

        console.log("Compiling CSS for " + themepath);
        return gulp.src('sass/**/*.scss', { cwd: themepath })
            .pipe(gulpif(argv.production !== 'false', sass({
                includePaths: ['./node_modules'],
            }).on('error', sass.logError), sass({
                style: 'expanded',
                sourceComments: 'normal'
            }).on('error', sass.logError)))
            .pipe(autoprefixer({
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
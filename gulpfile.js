const gulp = require('gulp');
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');
const concat = require('gulp-concat');
const imagemin = require('gulp-imagemin');
const wpPot = require('gulp-wp-pot');
const clean = require('gulp-clean');
const zip = require('gulp-zip');
const sourcemaps = require('gulp-sourcemaps');
const path = require('path');

// Paths
const paths = {
    scripts: {
        src: 'assets/js/**/*.js',
        dest: 'dist/js/'
    },
    styles: {
        src: 'assets/css/**/*.css',
        dest: 'dist/css/'
    },
    images: {
        src: 'assets/images/**/*.{jpg,jpeg,png,gif,svg}',
        dest: 'dist/images/'
    },
    translations: {
        src: '**/*.php',
        dest: 'languages/'
    },
    clean: 'dist/',
    zip: {
        src: 'dist/**',
        dest: 'release/',
        filename: 'pre-order-ultra.zip'
    }
};

// Clean Task
function cleanDist() {
    return gulp.src(paths.clean, { allowEmpty: true, read: false })
        .pipe(clean());
}

// Modify Scripts Task to include Source Maps
function scripts() {
    return gulp.src(paths.scripts.src, { sourcemaps: true })
        .pipe(sourcemaps.init())
        .pipe(concat('pre-order-ultra.min.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('.')) // Writes an external source map
        .pipe(gulp.dest(paths.scripts.dest));
}

// Modify Styles Task to include Source Maps
function styles() {
    return gulp.src(paths.styles.src, { sourcemaps: true })
        .pipe(sourcemaps.init())
        .pipe(concat('pre-order-ultra.min.css'))
        .pipe(cleanCSS())
        .pipe(sourcemaps.write('.')) // Writes an external source map
        .pipe(gulp.dest(paths.styles.dest));
}

// Images Task: Optimize Images
function images() {
    return gulp.src(paths.images.src)
        .pipe(imagemin())
        .pipe(gulp.dest(paths.images.dest));
}

// Translations Task: Generate POT file
function translations() {
    return gulp.src([paths.translations.src])
        .pipe(wpPot({
            domain: 'pre-order-ultra',
            package: 'Pre-Order Ultra',
            bugReport: 'https://yourplugin.com/support',
            team: 'Your Name <youremail@example.com>'
        }))
        .pipe(gulp.dest(paths.translations.dest + 'pre-order-ultra.pot'));
}

// Zip Task: Create ZIP archive
function zipFiles() {
    return gulp.src('dist/**')
        .pipe(zip(paths.zip.filename))
        .pipe(gulp.dest(paths.zip.dest));
}

// Watch Task
function watchFiles() {
    gulp.watch(paths.scripts.src, scripts);
    gulp.watch(paths.styles.src, styles);
    gulp.watch(paths.images.src, images);
    gulp.watch(paths.translations.src, translations);
}

// Define complex tasks
const build = gulp.series(cleanDist, gulp.parallel(scripts, styles, images, translations));
const watch = gulp.series(build, watchFiles);

// Export tasks
exports.clean = cleanDist;
exports.scripts = scripts;
exports.styles = styles;
exports.images = images;
exports.translations = translations;
exports.zip = gulp.series(build, zipFiles);
exports.build = build;
exports.watch = watch;
exports.default = build;
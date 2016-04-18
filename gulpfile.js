var gulp = require('gulp'),
    scsslint = require('gulp-scss-lint'),
    postcss = require('gulp-postcss'),
    cached = require('gulp-cached'),
    sass = require('gulp-sass');

var config = {
    paths: {
        sass: [
            'app/Resources/assets/scss/**/*.scss'
        ],
        fonts: [
            'app/Resources/assets/fonts/**/*.ttf'
        ],
        javascript: [
            'node_modules/jquery/dist/jquery.min.js'
        ]
    },
    autoprefixer: {
        browsers: [
            'last 2 versions',
            'safari 6',
            'ie 9',
            'opera 12.1',
            'ios 6',
            'android 4'
        ],
        cascade: true
    }
};

var processors = [
    require('autoprefixer')(config.autoprefixer)
];

gulp.task('sass-lint', function () {
    gulp.src(config.paths.sass)
        .pipe(cached('scsslint'))
        .pipe(scsslint({
            'config': 'app/config/.scss-lint.yml',
            'maxBuffer': 9999999
        }));
});

gulp.task('compile', ['sass', 'jquery', 'fonts'], function() {});

gulp.task('sass', function() {
    gulp.src('app/Resources/assets/scss/**/*.scss')
        .pipe(sass())
        .pipe(postcss(processors))
        .pipe(gulp.dest('web/css'));
});

gulp.task('watch', function () {
    gulp.watch(config.paths.sass, ['sass-lint', 'sass']);
});

gulp.task('jquery', function() {
    gulp.src(config.paths.javascript)
        .pipe(gulp.dest('web/js'));
});

gulp.task('fonts', function() {
    gulp.src(config.paths.fonts)
        .pipe(gulp.dest('web/fonts'));
});


gulp.task('default', ['compile', 'watch']);

var gulp = require('gulp'),
    sass = require('gulp-sass');

gulp.task('default', function() {
    gulp.src('app/Resources/assets/scss/**/*.scss')
        .pipe(sass())
        .pipe(gulp.dest('web/css'));
});

const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const uglify = require('gulp-uglifyjs');
const livereload = require('gulp-livereload');

gulp.task('sass', () => {
  return gulp.src('./web/themes/custom/test/sass/**/*.scss')
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(gulp.dest('./web/themes/custom/test/css'))
});

gulp.task('uglify', function() {
  gulp.src('./web/themes/custom/test/lib/*.js')
    .pipe(uglify('main.js'))
    .pipe(gulp.dest('./web/themes/custom/test/js'))
});

gulp.task('watch', function(){
  livereload.listen();

  gulp.watch('./web/themes/custom/test/sass/**/*.scss', gulp.series('sass'));
  gulp.watch('./web/themes/custom/test/lib/*.js', gulp.series('uglify'));
  gulp.watch(['./web/themes/custom/test/css/*.css', './web/themes/custom/test/**/*.twig', './web/themes/custom/test/js/*.js'], function (files){
    livereload.changed(files)
  });
});

gulp.task('default', gulp.parallel('sass', 'uglify', 'watch'));

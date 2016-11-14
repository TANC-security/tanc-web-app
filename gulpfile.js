var gulp = require('gulp'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    debug  = require('gulp-debug'),
    sass   = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    browserSync = require('browser-sync').create();

var DEST = 'templates/tanc';
var SRC  = 'bower_components/gentelella/';

gulp.task('scripts', function() {
    return gulp.src('bower_components/gentelella/src/js/*.js')
      .pipe(concat('custom.js'))
      .pipe(gulp.dest(DEST+'/js'))
      .pipe(rename({suffix: '.min'}))
      .pipe(uglify())
      .pipe(gulp.dest(DEST+'/js'))
      .pipe(browserSync.stream());
});
/*
gulp.task('project-scripts', function() {
    return gulp.src('bower_components/gentelella/src/js/*.js')
      .pipe(concat('custom.js'))
      .pipe(gulp.dest(DEST+'/js'))
      .pipe(rename({suffix: '.min'}))
      .pipe(uglify())
      .pipe(gulp.dest(DEST+'/js'))
      .pipe(browserSync.stream());
});
*/
/*
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
*/


gulp.task('plugins', function() {
    return gulp.src([
	'./bower_components/gentelella/vendors/jquery/dist/jquery.js',
	'./bower_components/gentelella/vendors/bootstrap/dist/js/bootstrap.js',
	SRC+'vendors/bootstrap-progressbar/bootstrap-progressbar.min.js',
	])
	.pipe(debug({title: 'plugins-js:'}))
      .pipe(concat('plugins.js'))
      .pipe(gulp.dest(DEST+'/js'))
      .pipe(rename({suffix: '.min'}))
      .pipe(uglify())
      .pipe(gulp.dest(DEST+'/js'))
      .pipe(browserSync.stream());
});



// TODO: Maybe we can simplify how sass compile the minify and unminify version
var compileSASS = function (filename, options) {
  return sass(SRC+'src/scss/*.scss', options)
        .pipe(autoprefixer('last 2 versions', '> 5%'))
        .pipe(concat(filename))
        .pipe(gulp.dest(DEST+'/css'))
        .pipe(browserSync.stream());
};

gulp.task('sass', function() {
    return compileSASS('custom.css', {});
});

gulp.task('sass-minify', function() {
    return compileSASS('custom.min.css', {style: 'compressed'});
});

gulp.task('plugins-css', function() {
    return gulp.src([
      SRC+'vendors/bootstrap/dist/css/bootstrap.min.css',
      SRC+'vendors/font-awesome/css/font-awesome.min.css',
      SRC+'vendors/iCheck/skins/flat/green.css',
      SRC+'vendors/animate.css/animate.css',
   ])
  .pipe(debug({title: 'plugins-css:'}))
      .pipe(concat('plugins-css.css'))
      .pipe(gulp.dest(DEST+'/css'));
    /*
      .pipe(rename({suffix: '.min'}))
      .pipe(uglify())
      .pipe(gulp.dest(DEST+'/css'));
    */
});


gulp.task('fonts', function() {
    return gulp.src([
	SRC+'vendors/fontawesome/fonts/fontawesome-webfont.woff',
	SRC+'vendors/fontawesome/fonts/fontawesome-webfont.woff2',
	])
      .pipe(gulp.dest(DEST+'/fonts'));
});

/*
    11     
    12     <!-- Bootstrap -->
    13     <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    14     <!-- Font Awesome -->
    15     <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    16     <!-- iCheck -->
    17     <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    18     <!-- bootstrap-progressbar -->
    19     <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    20     <!-- jVectorMap -->
    21     <link href="css/maps/jquery-jvectormap-2.0.3.css" rel="stylesheet"/>
    22     
    23     <!-- Custom Theme Style -->
    24     <link href="../build/css/custom.min.css" rel="stylesheet">
    25   </head>
    26     
	*/


gulp.task('browser-sync', function() {
    browserSync.init({
        server: {
            baseDir: './'
        },
        startPath: './production/index.html'
    });
});

gulp.task('watch', function() {
  // Watch .html files
  gulp.watch('production/*.html', browserSync.reload);
  // Watch .js files
  gulp.watch('src/js/*.js', ['scripts']);
  // Watch .scss files
  gulp.watch('src/scss/*.scss', ['sass', 'sass-minify']);
});

// Default Task
gulp.task('default', ['browser-sync', 'watch']);

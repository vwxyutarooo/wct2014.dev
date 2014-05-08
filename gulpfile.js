var gulp		=	require('gulp')
,	changed		=	require('gulp-changed')
,	concat		=	require('gulp-concat')
,	connect		=	require('gulp-connect')
,	filter		=	require('gulp-filter')
,	imagemin	=	require('gulp-imagemin')
,	jade		=	require('gulp-jade')
,	livereload	=	require('gulp-livereload')
,	minifycss	=	require('gulp-csso')
,	open		=	require('gulp-open')
,	plumber		=	require('gulp-plumber')
,	prefix		=	require('gulp-autoprefixer')
,	prettify	=	require('gulp-prettify')
,	rename		=	require('gulp-rename')
,	sass		=	require('gulp-ruby-sass')
;

var paths = {
	'dest':			''
,	'htmlDest':		'src/html'
,	'htmlDir':		'src/html/*.html'
,	'imgDest':		'images'
,	'imgDir':		'src/img/**'
,	'jadeDir':		'src/jade/*.jade'
,	'jsConcat':		'src/js/_*.js'
,	'jsDest':		'src/js'
,	'jsDir':		'src/js/*.js'
,	'scssDest':		'src/scss'
,	'scssDir':		['src/scss/*.scss', 'src/scss/*.sass']
}

gulp.task('connect', function() {
	connect.server({
		root: [__dirname + '/'],
		port: 1337,
		livereload: false
	});
});

gulp.task("open", function(){
	var options = { url: "http://localhost:1337/src/html/" };
	gulp.src("./src/html/index.html")
		.pipe(open("", options));
});

gulp.task('init', function() {
	gulp.src('bower_components/foundation/css/foundation.css')
		.pipe(rename({ prefix: '_', extname: '.scss' }))
		.pipe(gulp.dest(paths.scssDest))
		.pipe(gulp.src('bower_components/foundation-icon-fonts/foundation-icons.css'))
		.pipe(rename({ prefix: '_', extname: '.scss' }))
		.pipe(gulp.dest(paths.scssDest))
});

gulp.task('jade', function() {
	return gulp.src(paths.jadeDir)
		.pipe(changed(paths.htmlDest, { extension: '.html' }))
		.pipe(jade())
		.pipe(gulp.dest(paths.htmlDest))
		.pipe(prettify())
		.pipe(gulp.dest(paths.htmlDest))
		.pipe(connect.reload());
});

gulp.task('concat', function() {
	return gulp.src(paths.jsConcat)
		.pipe(concat('jquery.plugins.js'))
		.pipe(gulp.dest('./js'))
		.pipe(gulp.src('src/js/scripts.js'))
		.pipe(gulp.dest('./js'));
});

gulp.task('scss', function() {
	return gulp.src(paths.scssDir)
		.pipe(plumber({ errorHandler: handleError }))
		.pipe(sass({ style: 'expanded' }))
		.pipe(prefix('last 2 version'))
		.pipe(gulp.dest(paths.dest))
		.pipe(rename({suffix: '.min'}))
		.pipe(minifycss())
		.pipe(gulp.dest(paths.dest))
		.pipe(livereload());
});

gulp.task('image', function() {
	return gulp.src(paths.imgDir)
		.pipe(changed(paths.imgDest))
		.pipe(imagemin({optimizationLevel: 3}))
		.pipe(gulp.dest(paths.imgDest))
		.pipe(livereload());
});

gulp.task('watch', function() {
	gulp.watch([paths.jadeDir], ['jade']);
	gulp.watch([paths.imgDir], ['image']);
	//gulp.watch([paths.jsDir], ['concat']);
	gulp.watch([paths.scssDir], ['scss']);
	gulp.watch(['./*.php'], ['php']);
});

gulp.task('php', function() {
	return gulp.src('./*.php')
		.pipe(changed('./*.php'))
		.pipe(livereload());
});

function handleError(err) {
	console.log(err.toString());
	this.emit('end');
}

gulp.task('default', [
	'connect',
	'image',
	'jade',
	'php',
	'scss',
	'watch'
]);

gulp.task('begin', [
	'connect',
	'open',
	'scss',
	'jade',
	'php',
	'image',
	'watch'
]);
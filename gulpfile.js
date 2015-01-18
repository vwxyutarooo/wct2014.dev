/*******************************************************************************
 * 1. DEPENDENCIES
*******************************************************************************/
var gulp = require('gulp'),
	$ = require('gulp-load-plugins')({
			pattern: ['gulp-*', 'gulp.*'],
			replaceString: /\bgulp[\-.]/
		}),
	browserSync = require('browser-sync')
;

/*******************************************************************************
 * 2. FILE DESTINATIONS (RELATIVE TO ASSSETS FOLDER)
*******************************************************************************/
var paths = {
	'dest':			'./'
,	'htmlDest':		'src/html'
,	'htmlFiles':	'src/html/*.html'
,	'imgDest':		'files'
,	'imgDir':		'files/**'
,	'jadeDir':		'src/jade/*.jade'
,	'jadeFiles':	['src/jade/*.jade', 'src/jade/**/*.jade']
,	'jsAppFiles':	'src/js/*.js'
,	'jsDest':		'js'
,	'jsDir':		'src/js'
,	'jsLibFiles':	'src/lib/*.js'
,	'phpFiles':		['*.php', './**/*.php']
,	'scssDest':		'src/scss'
,	'scssDir':		['src/scss/**/*.scss', 'src/scss/**/*.sass']
,	'vhost':		'wct2014.dev'
,	'portNum':		3000
}

/*******************************************************************************
 * 3. initialize browser-sync && bower_components
*******************************************************************************/
gulp.task('bower-init', function(){
	var filterJs = $.filter('*.js');
	var filterCss = $.filter('*.css');
	bowerFiles().pipe(gulp.dest('src/lib'))
	gulp.src('src/lib/**')
		.pipe(filterJs)
		.pipe($.concat('lib.js'))
		.pipe($.uglify())
		.pipe($.rename({ suffix: '.min' }))
		.pipe(gulp.dest(paths.jsDest))
		.pipe(filterJs.restore())
		.pipe(filterCss)
		.pipe($.rename({ prefix: '_module-', extname: '.scss' }))
		.pipe($.flatten())
		.pipe(gulp.dest('src/scss/module'));
});

gulp.task('foundation-init', function() {
	var filter = $.filter(['foundation.scss', 'normalize.scss']);
	gulp.src('src/lib/foundation/scss/**')
		.pipe(filter)
		.pipe($.rename({ prefix: '_' }))
		.pipe(filter.restore())
		.pipe(gulp.dest('src/scss/core'));
});

gulp.task('browser-sync', function() {
	browserSync.init('./*.css', {
		proxy: paths.vhost,
		open: 'external'
	});
});

gulp.task('bs-reload', function() {
	browserSync.reload()
});

/*******************************************************************************
 * 4. Jade Tasks
*******************************************************************************/
gulp.task('jade', function() {
	return gulp.src(paths.jadeDir)
		.pipe($.plumber())
		.pipe($.jade())
		.pipe($.rename({ extname: '.php' }))
		.pipe(gulp.dest(paths.htmlDest))
		.pipe($.prettify({ indent_size: 2 }))
		.pipe($.rename({ extname: '.html' }))
		.pipe(gulp.dest(paths.htmlDest))
		.pipe(browserSync.reload({ stream: true, once: true }));
});

gulp.task('jade', function() {
	return gulp.src(paths.jadeDir)
		.pipe($.data(function(file) {
			return require('./setting.json');
		}))
		.pipe($.changed(paths.htmlDest, { extension: '.html' }))
		.pipe($.plumber())
		.pipe($.jade({ pretty: true }))
		.pipe(gulp.dest(paths.htmlDest))
		.pipe(browserSync.reload({ stream: true }));
});

/*******************************************************************************
 * 5. js Tasks
*******************************************************************************/
gulp.task('js', function() {
	return gulp.src('src/lib/app/*.js')
		.pipe($.sourcemaps.init())
		.pipe($.concat('script.js'))
		.pipe($.uglify())
		.pipe($.rename({ suffix: '.min' }))
		.pipe($.sourcemaps.write())
		.pipe(gulp.dest(paths.jsDest));
});

/*******************************************************************************
 * 6. sass Tasks
********************************************************************************/
gulp.task('scss', function() {
	return gulp.src(paths.scssDir)
		.pipe($.plumber({ errorHandler: handleError }))
		.pipe($.rubySass({
			r: "sass-globbing",
			'sourcemap=none': true
			// sourcemap: none // #113 "Try updating to master. A fix for this went in but I won't be releasing anything until 1.0."
		}))
		.pipe($.autoprefixer('last 2 version'))
		// .pipe($.csso())
		.pipe(gulp.dest(paths.dest));
});

function handleError(err) {
	console.log(err.toString());
	this.emit('end');
}

/*******************************************************************************
 * 7. Image file tasks
*******************************************************************************/
gulp.task('image', function() {
	return gulp.src(paths.imgDir)
		.pipe($.changed(paths.imgDest))
		.pipe($.imagemin({ optimizationLevel: 3 }))
		.pipe(gulp.dest(paths.imgDest))
		.pipe(browserSync.reload({ stream: true }));
});

gulp.task('sprite-icons', function () {
	var spriteData = gulp.src('src/img/sprite-icons/*.png')
	.pipe($.spritesmith({
		imgName: paths.imgDest + '/2014/10/sprite-icons.png',
		cssName: '_module-sprite-icons.scss'
	}));
	spriteData.img.pipe(gulp.dest('./'));
	spriteData.css.pipe(gulp.dest(paths.scssDest + '/module'));
});

/*******************************************************************************
 * 8. gulp Tasks
*******************************************************************************/
gulp.task('watch', function() {
	gulp.watch([paths.jadeFiles], ['jade']);
	// gulp.watch([paths.imgDir], ['image']);
	gulp.watch(['src/img/sprite-icons/*.png'], ['sprite-icons']);
	// gulp.watch([paths.jsDir], ['concat']);
	gulp.watch([paths.scssDir], ['scss']);
	gulp.watch([paths.phpFiles], ['bs-reload']);
});

gulp.task('default', [
	'browser-sync',
	'scss',
	'jade',
	// 'image',
	'sprite-icons',
	'watch'
]);

gulp.task('init', [
	'bower-init',
	'foundation-init'
]);

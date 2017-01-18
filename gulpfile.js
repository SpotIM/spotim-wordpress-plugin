/**
 *  Gulp File
 *
 *  Used for automating development tasks.
 */

/* Modules (Can be installed with npm install command using package.json)
 ------------------------------------- */
var gulp = require('gulp'),
    sort = require('gulp-sort'),
    wpPot = require('gulp-wp-pot'),
    checktextdomain = require('gulp-checktextdomain');

/* POT file task
 ------------------------------------- */
gulp.task('pot', function () {
    return gulp.src('**/*.php')
        .pipe(sort())
        .pipe(wpPot({
            package: 'Spot.IM Comments',
            domain: 'spotim-comments', //textdomain
            destFile: 'spotim-comments.pot',
            bugReport: 'https://github.com/SpotIM/wordpress-comments-plugin',
            lastTranslator: '',
            team: 'Spot.IM <support@spot.im>'
        }))
        .pipe(gulp.dest('language'));
});

/* Text-domain task
 ------------------------------------- */
gulp.task('textdomain', function () {
    var options = {
        text_domain: 'spotim-comments',
        keywords: [
            '__:1,2d',
            '_e:1,2d',
            '_x:1,2c,3d',
            'esc_html__:1,2d',
            'esc_html_e:1,2d',
            'esc_html_x:1,2c,3d',
            'esc_attr__:1,2d', 
            'esc_attr_e:1,2d', 
            'esc_attr_x:1,2c,3d', 
            '_ex:1,2c,3d',
            '_n:1,2,4d', 
            '_nx:1,2,4c,5d',
            '_n_noop:1,2,3d',
            '_nx_noop:1,2,3c,4d'
        ],
		correct_domain: true
    };
    return gulp.src('**/*.php')
        .pipe(checktextdomain(options))
});

/* Default Gulp task
 ------------------------------------- */
gulp.task('default', function () {
    // Run all the tasks!
    gulp.start('textdomain','pot');
});
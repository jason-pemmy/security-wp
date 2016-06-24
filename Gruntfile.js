'use strict';
module.exports = function (grunt){
	var server_host = 'security.tbkdev.com',
		theme_name= 'tbk-base',
		the_root_path = 'src/wp-content/themes/' + theme_name + '/',
		bootstrap_minified_file = the_root_path + '/js/vendor/bootstrap.min.js',
		minified_css_path = 'src/wp-content/themes/' + theme_name + '/css/minified/styles.min.css',
		blessed_css_path = 'src/wp-content/themes/' + theme_name + '/css/blessed',
		blessed_css_filename = blessed_css_path + '/styles.css',
		bootstrapFileList = [
			the_root_path + 'js/vendor/bootstrap/transition.js',
			the_root_path + 'js/vendor/bootstrap/alert.js',
			the_root_path + 'js/vendor/bootstrap/button.js',
			the_root_path + 'js/vendor/bootstrap/carousel.js',
			the_root_path + 'js/vendor/bootstrap/collapse.js',
			the_root_path + 'js/vendor/bootstrap/dropdown.js',
			the_root_path + 'js/vendor/bootstrap/modal.js',
			the_root_path + 'js/vendor/bootstrap/tooltip.js',
			the_root_path + 'js/vendor/bootstrap/popover.js',
			the_root_path + 'js/vendor/bootstrap/scrollspy.js',
			the_root_path + 'js/vendor/bootstrap/tab.js',
			the_root_path + 'js/vendor/bootstrap/affix.js'
		],
		cssPath = the_root_path + 'css/unminified/',
		build_minified_css_list = function(cssFiles) {
			var builtArray = [];

			cssFiles.forEach(function(cssFile) {
				builtArray.push(cssPath + cssFile + '.css');
			});

			return builtArray;
		};

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		less: {
			development: {
				options: {
					cleancss: true
				},
				files: [{
					expand: true,
					cwd: the_root_path + 'less/',
					src: [
						'**/*.less',
						'!**/bootstrap/**/*',
					],
					dest: the_root_path + 'css/unminified',
					flatten: true,
					ext: '.css'
				}]
			}
		},
		jshint: {
			all: [
				the_root_path + 'Gruntfile.js',
				the_root_path + 'js/scripts.js'
			]
		},
		uglify: {
			options: {
				mangle: false,
				preserveComments: 'some'
			},
			bootstrap_js: {
				files: {
					[bootstrap_minified_file]: [bootstrapFileList]
				}
			}
		},
		watch: {
			options: {
				interrupt: true,
				livereload: true,
				preserveComments: 'some'
			},
			styles: {
				files: [
					the_root_path + 'less/**/*.less'
				],
				tasks: ['newer:less']
			},
			scripts: {
				files: the_root_path + '**/*.js',
				tasks: ['newer:jshint', 'newer:uglify']
			}
		},
		browserSync: {
			dev: {
				bsFiles: {
					src: [
						the_root_path + 'css/**/*.css',
						the_root_path + '**/*.php',
						the_root_path + 'js/**/*.js'
					]
				},
				options: {
					watchTask: true,
					proxy: 'localhost/dev/' + server_host + '/src'
				}
			}
		},
		imagemin: {
			dynamic: {
				files: [{
					expand: true,
					cwd: 'src/wp-content/themes',
					src: ['**/*.{png,jpg,gif,jpeg}'],
					dest: 'src/wp-content/themes'
				}]
			}
		},
		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: -1,
				keepSpecialComments: 0
			},
			target: {
				files: {
					[minified_css_path]: build_minified_css_list([
						// vendors - all 3rd party styles come first
						'bootstrap',
						'js_composer',

						// base

						// layout

						// components

						// pages

						// shame file
						'shame',
					])
				}
			}
		},
		bless: {
			css: {
				options: {
					imports: false,
					compress: true,
				},
				files: {
					[blessed_css_filename]: [minified_css_path]
				}
			}
		},
		postcss: {
			autoprefixer: {
				options: {
					processors: [
						require('autoprefixer')({
							browsers: [
								'Chrome >= 35',
								'Firefox >= 29',
								'Explorer >= 10',
								'Edge >= 12',
								'iOS >= 9',
								'Safari >= 9',
							]
						}),
					]
				},
				dist: {
					files: {
						src: minified_css_path,
						dest: minified_css_path,
					}
				}
			},
			cssnano: {
				options: {
					processors: [
						require('cssnano')(),
					]
				},
				dist: {
					expand: true,
					src: blessed_css_path + '/*.css',
					dest: blessed_css_path
				}
			}
		}
	});

	// Load tasks
	grunt.loadNpmTasks('grunt-bless');
	grunt.loadNpmTasks('grunt-browser-sync');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-imagemin');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-newer');
	grunt.loadNpmTasks('grunt-postcss');

	// Register tasks
	grunt.registerTask('default', [
		'less'
	]);
	grunt.registerTask('dev', [
		'watch'
	]);
	grunt.registerTask('crunch', [
		'imagemin'
	]);
	grunt.registerTask('sync', [
		'browserSync',
		'watch'
	]);
	grunt.registerTask('minify', [
		'less',
		'cssmin',
		'postcss:autoprefixer',
		'bless',
		'postcss:cssnano'
	]);
};

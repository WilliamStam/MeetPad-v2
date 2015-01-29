module.exports = function(grunt) {
	require("matchdep").filterDev("grunt-*").forEach(grunt.loadNpmTasks);
	grunt.initConfig({
		concat: {
			js: {
				options: {
					separator: ';',
					stripBanners: true
				},
				src: [
					'vendor/components/jquery/jquery.js',
					'vendor/components/jqueryui/jquery-ui.js',
					'vendor/components/bootstrap/js/bootstrap.js' ,
					'vendor/components/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js',
					'vendor/components/jquery-bbq/jquery.ba-bbq.js',
					'vendor/components/jquery-hotkeys/jquery.hotkeys.js',
					'vendor/components/jquery-cookie/jquery.cookie.js',
					'vendor/components/jquery-jScrollPane/script/jquery.jScrollPane.js',
					'vendor/components/jquery-mousewheel/jquery.mousewheel.js',
					'vendor/components/jQote2/jquery.jqote2.js',
					'vendor/timrwood/moment/moment.js',
					'vendor/moxiecode/plupload/js/moxie.js',
					'vendor/ivaynberg/select2/select2.js',
					'ui/js/script.js'
				],
				dest: 'ui/javascript.js',
				nonull: true
			}
		},
		uglify: {
			options: {
				spawn: false,
				banner: '/*! <%= grunt.template.today("dd-mm-yyyy") %> */\n'
			},
			js: {
				files: {
					'ui/javascript.js': ['ui/javascript.js']
				}
			}
		},
		less: {
			style: {
				files: {
					"ui/style.css": "ui/less/style.less"
				}
			}
		},
		cssmin: {
			options: {
				report: "min",
				keepSpecialComments: 0
			},
			target: {
				files: {
					'ui/style.css': 'ui/style.css'
				}
			}
		},
		watch: {
			js: {
				files: ['js/*.js'],
				tasks: ['concat:js'],
				options: {
					spawn: false,
					livereload: true
				}
			},
			css: {
				files: ['less/*.less'],
				tasks: ['less:style'],
				options: {
					spawn: false,
					livereload: true
				}
			}
		}
	});

	
	


	
	
	grunt.registerTask('jsmin', ['uglify:js']);
	grunt.registerTask('js', ['concat:js']);
	grunt.registerTask('css', ['less:style']);
	grunt.registerTask('build', ['concat:js','less:style', 'uglify:js','cssmin']);
	grunt.registerTask('default', ['watch']);

};
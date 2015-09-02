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
					
					'vendor/components/jquery-hotkeys/jquery.hotkeys.js',
					'vendor/components/jquery-cookie/jquery.cookie.js',
					'vendor/components/jquery-jScrollPane/script/jquery.jScrollPane.js',
					'vendor/components/jquery-mousewheel/jquery.mousewheel.js',
					'vendor/components/TouchSwipe-Jquery-Plugin/jquery.touchSwipe.js',
					'vendor/components/jQote2/jquery.jqote2.js',
					//'vendor/ckeditor/ckeditor/ckeditor.js',
					'vendor/timrwood/moment/moment.js',
					'vendor/components/bootstrap-datetimepicker/src/js/bootstrap-datetimepicker.js',
					'vendor/moxiecode/plupload/js/plupload.full.min.js',
					'vendor/moxiecode/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js',
					//'vendor/plupload/js/plupload.full.js',
					//'vendor/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js',
					'vendor/ivaynberg/select2/select2.js',
					'app/_js/plugins/jquery.getData.js',
					'app/_js/plugins/jquery.ba-dotimeout.min.js',
					'app/_js/plugins/jquery.ba-bbq.js',
					'app/_js/plugins/jquery.ui.touch-punch.min.js',
					'app/_js/plugins/jquery.keepalive.js',
					'app/_js/script.js'
				],
				dest: 'app/javascript.js',
				nonull: true
			}
		},
		uglify: {
			options: {
				spawn: false,
				banner: '/*! Build date: <%= grunt.template.today("dd-mm-yyyy") %> */\n'
			},
			js: {
				files: {
					'app/javascript.js': ['app/javascript.js']
				}
			}
		},
		less: {
			style: {
				files: {
					"app/style.css": "app/less/style.less",
					"app/style-print.css": "app/less/style-print.less",
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
					'app/style.css': 'app/style.css',
					'app/style-print.css': 'app/style-print.css',
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
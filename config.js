module.exports = {
	config: {
		combine_files: {
			append: 'vsp-append',
			prepend: 'vsp-prepend',
			inline: 'vsp-inline',
		},
	},
	files: {
		'src/scss/vsp-addons.scss': {
			scss: true,
			autoprefixer: true,
			minify: true,
			dist: 'assets/css',
			watch: true,
			rename: 'vsp-addons.css',
		},
		'src/scss/vsp-framework.scss': {
			scss: true,
			autoprefixer: true,
			minify: true,
			watch: true,
			dist: 'assets/css',
			rename: 'vsp-framework.css',
		},
		'src/js/vsp-addons.js': {
			dist: 'assets/js',
			babel: true,
			rename: 'vsp-addons.js',
			watch: true,
			uglify: true,
		},
		'src/js/vsp-framework.js': {
			dist: 'assets/js',
			//babel: true,
			webpack: true,
			watch: true,
			uglify: true,
			rename: 'vsp-framework.js',
		},
		'src/js/vsp-plugin-dev.js': {
			dist: 'assets/js',
			combine_files: true,
			uglify: true,
			watch: true,
			rename: 'vsp-plugins.js',
		}
	}
};
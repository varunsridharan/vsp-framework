module.exports = {
	config: {
		combine_files: {
			append: 'vsp-append',
			prepend: 'vsp-prepend',
			inline: 'vsp-inline',
		},
	},
	files: {
		'src/scss/vsp-framework.scss': {
			scss: true,
			autoprefixer: true,
			minify: true,
			watch: true,
			dist: 'assets/css',
			rename: 'vsp-framework.css',
		},
		'src/js/vsp-framework.js': {
			dist: 'assets/js',
			//babel: true,
			webpack: true,
			watch: true,
			uglify: true,
			rename: 'vsp-framework.js',
		},
	}
};
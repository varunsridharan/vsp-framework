module.exports = {
	config: {
		combine_files: {
			append: 'vsp-append',
			prepend: 'vsp-prepend',
			inline: 'vsp-inline',
		},
	},
	files: {
		'assets_src/scss/vsp-framework.scss': {
			scss: true,
			autoprefixer: true,
			minify: true,
			watch: true,
			dist: 'assets/css',
			rename: 'vsp-framework.css',
		},
		'assets_src/js/vsp-framework.js': {
			dist: 'assets/js',
			babel: true,
			webpack: false,
			watch: true,
			uglify: true,
			rename: 'vsp-framework.js',
		},
	}
};

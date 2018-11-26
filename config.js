let $_json          = {};
$_json.project_name = 'VSP Framework';
$_json.scss         = {
	'./src/scss/vsp-addons.scss': {
		scss: true,
		sourcemap: false,
		dist: 'assets/css',
		concat: 'vsp-addons.css',

	},
	'./src/scss/vsp-framework.scss': {
		scss: true,
		sourcemap: false,
		dist: 'assets/css',
		concat: 'vsp-framework.css',

	},
	'./src/scss/vsp-plugins.scss': {
		scss: true,
		sourcemap: false,
		dist: 'assets/css',
		concat: 'vsp-plugins.css',

	},
};
$_json.js           = {
	'./src/js/vsp-addons.js': {
		dist: 'assets/js',
		combine_files: false,
		concat_dev: 'vsp-addons.js',
		concat: 'vsp-addons.js',
		webpack: false,
		babel: true,
		webpack_dev: false,
	},
	'./src/js/vsp-framework.js': {
		dist: 'assets/js',
		combine_files: false,
		concat_dev: 'vsp-framework.js',
		concat: 'vsp-framework.js',
		webpack: true,
		babel: false,
		webpack_dev: true,
	},
	'./src/js/vsp-plugin-dev.js': {
		dist: 'assets/js',
		combine_files: true,
		concat_dev: 'vsp-plugins.js',
		concat: 'vsp-plugins.js',
		webpack: false,
		webpack_dev: false,
	},
};

$_json.wppot = {
	'./index.php': {
		dist: './languages/vsp-framework-2.pot',
		src: [
			'*.php',
			'core/*.php',
			'core/*/*.php',
			'core/*/**/*.php',
			'functions/*.php',
			'functions/*/*.php',
			'functions/*/**/*.php',
			'integrations/*.php',
			'integrations/*/*.php',
			'integrations/*/**/*.php',
			'libs/*.php',
			'libs/*/*.php',
			'libs/*/**/*.php',
			'views/*.php',
			'views/*/*.php',
			'views/*/**/*.php',
			'!node_modules/',
			'!assets/',
			'!src/',

		],
		wppot: {
			domain: 'vsp-framework',
			package: 'VSP Framework',
			bugReport: 'http://github.com/varunsridharan/vsp-framework',
		},
	},
};

/**
 * Settings any feature to false will not trigger for any files untill its
 * overridden in file config.
 * js:{
 *     "your_file_source":{
 *         scss:true,
 *         dist:"your_file_dist",
 *     }
 * }
 */
$_json.status = {
	scss: true,
	autoprefixer: true,
	sourcemap: true,
	webpack: true,
	babel: false,
	parcel: false,
	rollup: false,
	minify: true,
	uglify: true,
	combine_files: true,
	concat: true,
};
$_json.default_config = {
	/**
	 * Production Configs.
	 */
	combine_files: {
		append: 'vsp-append',
		prepend: 'vsp-prepend',
		inline: 'vsp-inline',
	},
	minify: {
		args: {},
		callback: false
	},
	concat: {},
	scss: {
		outputStyle: 'expanded'
	},
	sourcemap: '../maps',
	autoprefixer: {
		browsers: [ 'last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4' ],
		cascade: false
	},
	webpack: {
		mode: 'production',
		output: {
			filename: '[name].js',
		},
		target: 'node',
		module: {
			rules: [
				{
					test: /\.js$/,
					loader: 'babel-loader',
					options: {
						presets: [ 'es2015' ]
					}
				}
			]
		},
	},
	parcel: false,
	uglify: true,
	babel: {
		presets: [ '@babel/env' ],
	},
	wppot: {},

	/**
	 * Development Config.
	 */
	webpack_dev: {
		devtool: 'inline-source-map',
		mode: 'development',
		target: 'node',
		output: {
			filename: '[name].js',
		},
		module: {
			rules: [
				{
					test: /\.js$/,
					loader: 'babel-loader',
					options: {
						presets: [ 'es2015' ]
					}
				}
			]
		},
	},
	uglify_dev: false,
};
module.exports        = $_json;

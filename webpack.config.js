const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const WebpackBar = require( 'webpackbar' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

/*
 * `defaultConfig.plugins` holds shared plugin *instances*. Give each config
 * its own `DependencyExtractionWebpackPlugin` so that the configs below never
 * share state between compilers (see bm-block-library's webpack config).
 */
const withFreshExternals = ( plugins ) =>
	plugins.map( ( plugin ) =>
		plugin?.constructor?.name === 'DependencyExtractionWebpackPlugin'
			? new DependencyExtractionWebpackPlugin()
			: plugin
	);

/*
 * Editor script for the Track Timetable block.
 */
const blocks = {
	...defaultConfig,
	entry: {
		'track-timetable': './src/blocks/track-timetable/track-timetable.js',
	},
	output: {
		path: path.resolve( process.cwd(), 'dist' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	plugins: [
		...withFreshExternals( defaultConfig.plugins ),
		new WebpackBar( { name: 'Blocks', color: '#cd1cec' } ),
	],
};

/*
 * Front-end scripts and the stylesheet.
 */
const publicAssets = {
	...defaultConfig,
	entry: {
		'scripts/dist/app': './assets/scripts/src/index.js',
		'scripts/dist/live-viewer': './assets/scripts/src/live-viewer.js',
		'scripts/dist/time-switcher': './assets/scripts/src/time-switcher.js',
		'styles/dist/app': './assets/styles/src/app.scss',
	},
	output: {
		path: path.resolve( process.cwd(), 'assets' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	plugins: [
		...withFreshExternals( defaultConfig.plugins ),
		new WebpackBar( { name: 'Public', color: '#5aec1c' } ),
	],
};

module.exports = [ blocks, publicAssets ];

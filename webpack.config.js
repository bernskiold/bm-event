const path = require( 'path' );
const fastGlob = require( 'fast-glob' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const WebpackBar = require( 'webpackbar' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

/*
 * `defaultConfig.plugins` holds shared plugin *instances*. The module config
 * below runs in module mode, which would flip a shared
 * `DependencyExtractionWebpackPlugin` into module externalisation for the
 * script configs as well. Give each config a fresh instance, as in
 * bm-block-library.
 */
const withFreshExternals = ( plugins ) =>
	plugins.map( ( plugin ) =>
		plugin?.constructor?.name === 'DependencyExtractionWebpackPlugin'
			? new DependencyExtractionWebpackPlugin()
			: plugin
	);

/*
 * Editor scripts: blocks/<name>/index.js -> dist/blocks/<name>.js, which the
 * block's block.json references as its editorScript.
 */
const blocks = {
	...defaultConfig,
	entry: fastGlob.sync( './blocks/*/index.js' ).reduce(
		( entries, file ) => ( {
			...entries,
			[ path.basename( path.dirname( file ) ) ]: file,
		} ),
		{}
	),
	output: {
		path: path.resolve( process.cwd(), 'dist', 'blocks' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	plugins: [
		...withFreshExternals( defaultConfig.plugins ),
		new WebpackBar( { name: 'Blocks', color: '#cd1cec' } ),
	],
};

/*
 * The front-end stylesheet.
 */
const styles = {
	...defaultConfig,
	entry: {
		app: './assets/styles/src/app.scss',
	},
	output: {
		path: path.resolve( process.cwd(), 'assets', 'styles', 'dist' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	plugins: [
		...withFreshExternals( defaultConfig.plugins ),
		new WebpackBar( { name: 'Styles', color: '#5aec1c' } ),
	],
};

/*
 * Front-end script modules: the block's viewScriptModule and the
 * shortcode scripts, loaded with wp_register_script_module().
 */
const modules = {
	...defaultConfig,
	entry: {
		...fastGlob.sync( './blocks/*/view.js' ).reduce(
			( entries, file ) => ( {
				...entries,
				[ `dist/blocks/${ path.basename(
					path.dirname( file )
				) }/view` ]: file,
			} ),
			{}
		),
		'assets/scripts/dist/live-viewer':
			'./assets/scripts/src/live-viewer.js',
		'assets/scripts/dist/time-switcher':
			'./assets/scripts/src/time-switcher.js',
	},
	output: {
		path: process.cwd(),
		filename: '[name].js',
		chunkFilename: '[name].js',
		module: true,
		library: { type: 'module' },
		environment: { module: true, dynamicImport: true },
	},
	experiments: {
		...( defaultConfig.experiments || {} ),
		outputModule: true,
	},
	externalsType: 'module',
	plugins: [
		...withFreshExternals( defaultConfig.plugins ),
		new WebpackBar( { name: 'Modules', color: '#ec1c5f' } ),
	],
};

module.exports = [ blocks, styles, modules ];

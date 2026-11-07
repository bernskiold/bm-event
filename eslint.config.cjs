/**
 * ESLint flat config.
 *
 * Reuses the `@wordpress/scripts` default config, the same way as
 * bm-block-library, so the bundled `@wordpress/eslint-plugin` never drifts.
 */

/**
 * External dependencies
 */
const globals = require( 'globals' );

/**
 * Internal dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	{
		ignores: [
			'dist/**',
			'build/**',
			'assets/scripts/dist/**',
			'assets/styles/dist/**',
			'node_modules/**',
			'vendor/**',
			'languages/**',
		],
	},

	...defaultConfig,

	{
		languageOptions: {
			globals: {
				...globals.browser,
				wp: 'readonly',
			},
		},
		rules: {
			camelcase: [
				'error',
				{ properties: 'never', ignoreDestructuring: true },
			],
			'no-console': [ 'error', { allow: [ 'warn', 'error', 'info' ] } ],
			'jsdoc/require-param-type': 'off',
			'jsdoc/require-returns-description': 'off',
		},
	},
];

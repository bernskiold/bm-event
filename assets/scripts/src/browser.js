/**
 * Minimal cookie helpers, replacing js-cookie.
 *
 * Cookies are session cookies on the whole site (path "/"), the same
 * as js-cookie's defaults.
 */

/**
 * Read a cookie.
 *
 * @param {string} name
 *
 * @return {string|null} The value, or null when it is not set.
 */
export function getCookie( name ) {
	const prefix = `${ encodeURIComponent( name ) }=`;

	const cookie = document.cookie
		.split( '; ' )
		.find( ( item ) => item.startsWith( prefix ) );

	if ( ! cookie ) {
		return null;
	}

	try {
		return decodeURIComponent( cookie.slice( prefix.length ) );
	} catch {
		return null;
	}
}

/**
 * Set a cookie.
 *
 * @param {string} name
 * @param {string} value
 */
export function setCookie( name, value ) {
	document.cookie = `${ encodeURIComponent( name ) }=${ encodeURIComponent(
		value
	) }; path=/; SameSite=Lax`;
}

/**
 * Read the data WordPress printed for a script module.
 *
 * @param {string} moduleId
 *
 * @return {Object} The data, or an empty object.
 */
export function getModuleData( moduleId ) {
	const element = document.getElementById(
		`wp-script-module-data-${ moduleId }`
	);

	if ( ! element ) {
		return {};
	}

	try {
		return JSON.parse( element.textContent ) || {};
	} catch {
		return {};
	}
}

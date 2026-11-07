/**
 * Time zone helpers for the timetable, built on the browser's Intl API.
 *
 * These replace moment-timezone. Times in the markup are wall-clock
 * times in the site's time zone, formatted as "YYYY-MM-DD HH:mm".
 */

const DATETIME_PATTERN = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{1,2}):(\d{2})/;

/**
 * Zero-pad a number to two digits.
 *
 * @param {number} value
 *
 * @return {string} Padded value.
 */
const pad = ( value ) => String( value ).padStart( 2, '0' );

/**
 * Whether the browser knows the given IANA time zone.
 *
 * @param {string} timeZone
 *
 * @return {boolean} True when the time zone is valid.
 */
export function isValidTimezone( timeZone ) {
	if ( ! timeZone || 'string' !== typeof timeZone ) {
		return false;
	}

	try {
		new Intl.DateTimeFormat( 'en-US', { timeZone } );
		return true;
	} catch {
		return false;
	}
}

/**
 * Work out the site's time zone from its settings.
 *
 * WordPress stores either a city (timezone_string) or a plain UTC
 * offset in hours (gmt_offset). Whole-hour offsets map to the
 * equivalent Etc/GMT zone, and anything else falls back to UTC.
 *
 * @param {string}        timezoneString
 * @param {string|number} gmtOffset
 *
 * @return {string} An IANA time zone.
 */
export function getSiteTimezone( timezoneString, gmtOffset ) {
	if ( isValidTimezone( timezoneString ) ) {
		return timezoneString;
	}

	const offset = Number( gmtOffset );

	if ( ! offset || ! Number.isInteger( offset ) ) {
		return 'UTC';
	}

	// The sign of the Etc/GMT zones is inverted: UTC+2 is Etc/GMT-2.
	const etcZone = `Etc/GMT${ offset > 0 ? '-' : '+' }${ Math.abs( offset ) }`;

	return isValidTimezone( etcZone ) ? etcZone : 'UTC';
}

/**
 * The offset from UTC, in minutes, of a time zone at a given instant.
 *
 * @param {string} timeZone
 * @param {number} timestamp Milliseconds since the epoch.
 *
 * @return {number} Offset in minutes, positive east of UTC.
 */
export function getTimezoneOffset( timeZone, timestamp = Date.now() ) {
	const parts = new Intl.DateTimeFormat( 'en-US', {
		timeZone,
		hourCycle: 'h23',
		year: 'numeric',
		month: '2-digit',
		day: '2-digit',
		hour: '2-digit',
		minute: '2-digit',
		second: '2-digit',
	} ).formatToParts( new Date( timestamp ) );

	const value = ( type ) =>
		Number( parts.find( ( part ) => part.type === type ).value );

	const asUtc = Date.UTC(
		value( 'year' ),
		value( 'month' ) - 1,
		value( 'day' ),
		value( 'hour' ),
		value( 'minute' ),
		value( 'second' )
	);

	// Drop the milliseconds, which formatToParts does not include.
	const instant = Math.floor( timestamp / 1000 ) * 1000;

	return Math.round( ( asUtc - instant ) / 60000 );
}

/**
 * Format an offset in minutes like "+05:30".
 *
 * @param {number} minutes
 *
 * @return {string} Formatted offset.
 */
export function formatOffset( minutes ) {
	const sign = minutes < 0 ? '-' : '+';
	const absolute = Math.abs( minutes );

	return `${ sign }${ pad( Math.floor( absolute / 60 ) ) }:${ pad(
		absolute % 60
	) }`;
}

/**
 * Convert a wall-clock time from one time zone to another.
 *
 * @param {string} datetime       "YYYY-MM-DD HH:mm" in the source time zone.
 * @param {string} sourceTimeZone
 * @param {string} targetTimeZone
 *
 * @return {{datetime: string, time: string}|null} The converted date and time,
 *                                                 or null if it could not be parsed.
 */
export function convertTime( datetime, sourceTimeZone, targetTimeZone ) {
	const match = DATETIME_PATTERN.exec( String( datetime ).trim() );

	if ( ! match ) {
		return null;
	}

	const [ , year, month, day, hour, minute ] = match.map( Number );
	const wallClock = Date.UTC( year, month - 1, day, hour, minute );

	// Find the instant for this wall-clock time in the source zone. The
	// second pass corrects the offset when the first guess crosses a DST
	// change.
	let instant =
		wallClock - getTimezoneOffset( sourceTimeZone, wallClock ) * 60000;
	instant = wallClock - getTimezoneOffset( sourceTimeZone, instant ) * 60000;

	const local = new Date(
		instant + getTimezoneOffset( targetTimeZone, instant ) * 60000
	);

	const time = `${ pad( local.getUTCHours() ) }:${ pad(
		local.getUTCMinutes()
	) }`;

	return {
		datetime: `${ local.getUTCFullYear() }-${ pad(
			local.getUTCMonth() + 1
		) }-${ pad( local.getUTCDate() ) } ${ time }`,
		time,
	};
}

/**
 * All time zones known to the browser, sorted by their current offset.
 *
 * @param {number} timestamp Milliseconds since the epoch.
 *
 * @return {{name: string, offset: number, label: string}[]} Time zones.
 */
export function getTimezoneOptions( timestamp = Date.now() ) {
	const names =
		'function' === typeof Intl.supportedValuesOf
			? Intl.supportedValuesOf( 'timeZone' )
			: [ 'UTC' ];

	return names
		.map( ( name ) => {
			const offset = getTimezoneOffset( name, timestamp );
			const gmt = offset ? formatOffset( offset ) : '';

			return {
				name,
				offset,
				label: `(GMT${ gmt }) ${ name.replace( '/', ' - ' ) }`,
			};
		} )
		.sort( ( a, b ) => a.offset - b.offset );
}

/**
 * Guess the visitor's time zone.
 *
 * @return {string|null} An IANA time zone, or null.
 */
export function guessTimezone() {
	try {
		return Intl.DateTimeFormat().resolvedOptions().timeZone || null;
	} catch {
		return null;
	}
}

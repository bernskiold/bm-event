/**
 * Timezone selector for the Track Timetable block.
 *
 * Converts the session times on the page to the visitor's time zone, or
 * to the one they pick, and remembers the choice in a cookie.
 */

/**
 * Internal dependencies
 */
import {
	getCookie,
	getModuleData,
	setCookie,
} from '../../assets/scripts/src/browser';
import {
	convertTime,
	getSiteTimezone,
	getTimezoneOptions,
	guessTimezone,
	isValidTimezone,
} from './timezones';

const COOKIE = 'bm_events_tz';
const MODULE_ID = 'bm-track-timetable-view-script-module';

/**
 * Get the saved, guessed or site time zone, in that order.
 *
 * @param {string} siteTimezone
 *
 * @return {string} An IANA time zone.
 */
function getCurrentTimezone( siteTimezone ) {
	const saved = getCookie( COOKIE );

	if ( isValidTimezone( saved ) ) {
		return saved;
	}

	const guessed = guessTimezone();

	return isValidTimezone( guessed ) ? guessed : siteTimezone;
}

/**
 * Show all session times on the page in the given time zone.
 *
 * The original site time is kept in a data attribute so that every
 * conversion starts from it.
 *
 * @param {string} timezone
 * @param {string} siteTimezone
 */
function applyTimezone( timezone, siteTimezone ) {
	setCookie( COOKIE, timezone );

	document
		.querySelectorAll( '.js-event-start-time, .js-event-end-time' )
		.forEach( ( element ) => {
			if ( undefined === element.dataset.bmEventDatetime ) {
				element.dataset.bmEventDatetime =
					element.getAttribute( 'datetime' ) || '';
			}

			const converted = convertTime(
				element.dataset.bmEventDatetime,
				siteTimezone,
				timezone
			);

			if ( ! converted ) {
				return;
			}

			element.setAttribute( 'datetime', converted.datetime );
			element.textContent = converted.time;
		} );
}

/**
 * Render the time zone selector into its wrapper.
 *
 * @param {HTMLElement} wrapper
 * @param {Object}      texts
 * @param {string}      current
 *
 * @return {HTMLSelectElement} The select element.
 */
function renderSelector( wrapper, texts, current ) {
	const id = 'bm-events-tz-selector';

	const label = document.createElement( 'label' );
	label.htmlFor = id;
	label.textContent = texts.tzLabel || '';

	const select = document.createElement( 'select' );
	select.id = id;

	const placeholder = new Option( texts.selectTz || '', 'null' );
	placeholder.disabled = true;
	select.append( placeholder );

	getTimezoneOptions().forEach( ( { name, offset, label: text } ) => {
		const option = new Option( text, name, false, name === current );
		option.dataset.offset = String( offset );
		select.append( option );
	} );

	wrapper.replaceChildren( label, select );

	return select;
}

const wrapper = document.querySelector(
	'.js--track-timetable-timezone-selector'
);

// The selector is only rendered when enabled in the block settings.
if ( wrapper ) {
	const data = getModuleData( MODULE_ID );
	const siteTimezone = getSiteTimezone( data.siteTimezone, data.gmtOffset );
	const current = getCurrentTimezone( siteTimezone );

	const select = renderSelector( wrapper, data.texts || {}, current );

	applyTimezone( current, siteTimezone );

	select.addEventListener( 'change', () => {
		if ( isValidTimezone( select.value ) ) {
			applyTimezone( select.value, siteTimezone );
		}
	} );
}

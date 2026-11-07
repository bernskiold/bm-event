/**
 * Timezone selector for the [bm_display_events] shortcode.
 *
 * Stores the chosen time zone in a cookie and reloads the session grid,
 * which the server renders in that time zone.
 */

/**
 * Internal dependencies
 */
import { getModuleData, setCookie } from './browser';

const { apiUrl = '' } = getModuleData( 'bm-event-time-switcher' );

const timezoneSelector = document.querySelector( '.js--timezone-selector' );
const sessionGrid = document.querySelector( '.js--session-grid' );

const handleTimezoneSelection = ( event ) => {
	setCookie( 'event_timezone', event.target.value );

	window
		.fetch( `${ apiUrl }/session-grid` )
		.then( ( res ) => res.json() )
		.then( ( res ) => {
			if ( sessionGrid ) {
				sessionGrid.innerHTML = res.html;
			}
		} );
};

if ( timezoneSelector ) {
	timezoneSelector.addEventListener( 'change', handleTimezoneSelection );
}

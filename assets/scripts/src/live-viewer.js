/**
 * Live viewer for the [bm_liveviewer] shortcode.
 *
 * Polls the REST API and reloads the page when the next session is
 * about to start, so that its video is loaded.
 */

/**
 * Internal dependencies
 */
import { getModuleData } from './browser';

const { apiUrl = '', pollingInterval = 30000 } = getModuleData(
	'bm-event-live-viewer'
);

const player = document.querySelector( '.session-player' );
const eventId = player ? parseInt( player.dataset.eventId, 10 ) || 0 : 0;

const checkForNextVideo = () =>
	window
		.fetch( `${ apiUrl }/next-video` )
		.then( ( res ) => res.json() )
		.then( ( res ) => {
			if ( res.id !== eventId && res.should_refresh ) {
				window.location.reload();
			}
		} )
		.catch( () => {} );

if ( player && eventId !== 0 ) {
	window.setInterval( checkForNextVideo, pollingInterval );
	checkForNextVideo();
}

/**
 * The timezone selector view module, run against the DOM.
 */

/**
 * External dependencies
 */
import { beforeEach, describe, expect, it, vi } from 'vitest';

const MODULE_DATA = {
	siteTimezone: 'Europe/London',
	gmtOffset: '0',
	texts: {
		selectTz: 'Select Your Timezone...',
		tzLabel: 'See the schedule in your local time.',
	},
};

function render( { withSelector = true } = {} ) {
	document.body.innerHTML = `
		<script type="application/json" id="wp-script-module-data-bm-track-timetable-view-script-module">${ JSON.stringify(
			MODULE_DATA
		) }</script>
		<div class="sessions">
			${
				withSelector
					? '<div class="js--track-timetable-timezone-selector"></div>'
					: ''
			}
			<time class="js-event-start-time" datetime="2026-01-15 09:00">09:00</time>
			<time class="js-event-end-time" datetime="2026-01-15 10:30">10:30</time>
		</div>
	`;
}

async function loadView() {
	vi.resetModules();
	await import( '../../blocks/track-timetable/view' );
}

const clearCookie = () => {
	document.cookie =
		'bm_events_tz=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
};

describe( 'timezone selector', () => {
	beforeEach( clearCookie );

	it( 'does nothing when the selector is turned off', async () => {
		render( { withSelector: false } );
		await loadView();

		expect( document.querySelector( 'select' ) ).toBeNull();
		expect(
			document.querySelector( '.js-event-start-time' ).textContent
		).toBe( '09:00' );
	} );

	it( 'shows the times in the saved time zone', async () => {
		document.cookie = 'bm_events_tz=Europe%2FStockholm; path=/';
		render();
		await loadView();

		const select = document.querySelector( 'select' );

		expect( select.value ).toBe( 'Europe/Stockholm' );
		expect( document.querySelector( 'label' ).getAttribute( 'for' ) ).toBe(
			select.id
		);

		const start = document.querySelector( '.js-event-start-time' );
		expect( start.textContent ).toBe( '10:00' );
		expect( start.getAttribute( 'datetime' ) ).toBe( '2026-01-15 10:00' );
		expect(
			document.querySelector( '.js-event-end-time' ).textContent
		).toBe( '11:30' );
	} );

	it( 'converts from the original time when the zone changes', async () => {
		document.cookie = 'bm_events_tz=Europe%2FStockholm; path=/';
		render();
		await loadView();

		const select = document.querySelector( 'select' );

		select.value = 'America/New_York';
		select.dispatchEvent( new Event( 'change' ) );

		expect(
			document.querySelector( '.js-event-start-time' ).textContent
		).toBe( '04:00' );
		expect( document.cookie ).toContain(
			'bm_events_tz=America%2FNew_York'
		);

		select.value = 'Europe/London';
		select.dispatchEvent( new Event( 'change' ) );

		expect(
			document.querySelector( '.js-event-start-time' ).textContent
		).toBe( '09:00' );
	} );

	it( 'ignores an invalid saved zone', async () => {
		document.cookie = 'bm_events_tz=Not%2FAZone; path=/';
		render();
		await loadView();

		expect( document.querySelector( 'select' ).value ).not.toBe(
			'Not/AZone'
		);
	} );
} );

/**
 * External dependencies
 */
import { describe, expect, it } from 'vitest';

/**
 * Internal dependencies
 */
import {
	convertTime,
	formatOffset,
	getSiteTimezone,
	getTimezoneOffset,
	getTimezoneOptions,
	isValidTimezone,
} from '../../blocks/track-timetable/timezones';

describe( 'isValidTimezone', () => {
	it( 'accepts IANA time zones', () => {
		expect( isValidTimezone( 'Europe/Stockholm' ) ).toBe( true );
		expect( isValidTimezone( 'UTC' ) ).toBe( true );
	} );

	it( 'rejects anything else', () => {
		expect( isValidTimezone( 'Not/AZone' ) ).toBe( false );
		expect( isValidTimezone( '' ) ).toBe( false );
		expect( isValidTimezone( null ) ).toBe( false );
		expect( isValidTimezone( 'null' ) ).toBe( false );
	} );
} );

describe( 'getSiteTimezone', () => {
	it( 'uses the timezone string when set', () => {
		expect( getSiteTimezone( 'Europe/Oslo', '0' ) ).toBe( 'Europe/Oslo' );
	} );

	it( 'maps whole-hour offsets to Etc/GMT zones', () => {
		expect( getSiteTimezone( '', '2' ) ).toBe( 'Etc/GMT-2' );
		expect( getSiteTimezone( '', -5 ) ).toBe( 'Etc/GMT+5' );
	} );

	it( 'falls back to UTC', () => {
		expect( getSiteTimezone( '', '0' ) ).toBe( 'UTC' );
		expect( getSiteTimezone( '', '5.5' ) ).toBe( 'UTC' );
		expect( getSiteTimezone( undefined, undefined ) ).toBe( 'UTC' );
	} );
} );

describe( 'getTimezoneOffset', () => {
	it( 'follows daylight saving time', () => {
		expect(
			getTimezoneOffset( 'Europe/Stockholm', Date.UTC( 2026, 0, 15 ) )
		).toBe( 60 );
		expect(
			getTimezoneOffset( 'Europe/Stockholm', Date.UTC( 2026, 6, 15 ) )
		).toBe( 120 );
		expect(
			getTimezoneOffset( 'America/New_York', Date.UTC( 2026, 0, 15 ) )
		).toBe( -300 );
		expect( getTimezoneOffset( 'Asia/Kolkata' ) ).toBe( 330 );
	} );
} );

describe( 'formatOffset', () => {
	it( 'formats like moment’s "Z" token', () => {
		expect( formatOffset( 330 ) ).toBe( '+05:30' );
		expect( formatOffset( -210 ) ).toBe( '-03:30' );
		expect( formatOffset( 0 ) ).toBe( '+00:00' );
	} );
} );

describe( 'convertTime', () => {
	it( 'converts between time zones', () => {
		expect(
			convertTime(
				'2026-07-01 10:00',
				'Europe/Stockholm',
				'America/New_York'
			)
		).toEqual( { datetime: '2026-07-01 04:00', time: '04:00' } );
	} );

	it( 'rolls over to the next day', () => {
		expect(
			convertTime( '2026-11-07 23:30', 'UTC', 'Asia/Kolkata' )
		).toEqual( { datetime: '2026-11-08 05:00', time: '05:00' } );
	} );

	it( 'uses the offset in effect on the date, not today', () => {
		expect(
			convertTime(
				'2026-01-15 09:00',
				'Europe/London',
				'Europe/Stockholm'
			).time
		).toBe( '10:00' );
		expect(
			convertTime( '2026-03-30 09:00', 'Europe/London', 'UTC' ).time
		).toBe( '08:00' );
	} );

	it( 'returns the same time for the same zone', () => {
		expect(
			convertTime( '2026-05-01 09:15', 'Europe/Paris', 'Europe/Paris' )
		).toEqual( { datetime: '2026-05-01 09:15', time: '09:15' } );
	} );

	it( 'returns null when it cannot parse the time', () => {
		expect( convertTime( ' 10:00', 'UTC', 'UTC' ) ).toBeNull();
		expect( convertTime( '', 'UTC', 'UTC' ) ).toBeNull();
	} );
} );

describe( 'getTimezoneOptions', () => {
	const options = getTimezoneOptions( Date.UTC( 2026, 0, 15 ) );

	it( 'is sorted by offset', () => {
		const offsets = options.map( ( option ) => option.offset );

		expect( offsets ).toEqual( [ ...offsets ].sort( ( a, b ) => a - b ) );
	} );

	it( 'labels zones like the old select2 options', () => {
		expect(
			options.find( ( option ) => option.name === 'Europe/Stockholm' )
				.label
		).toBe( '(GMT+01:00) Europe - Stockholm' );
		expect(
			options.find( ( option ) => option.name === 'Europe/London' ).label
		).toBe( '(GMT) Europe - London' );
	} );
} );

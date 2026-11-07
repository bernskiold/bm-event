/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder, SelectControl, Spinner } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import SessionGridItem from './components/session-grid-item';
import SessionListItem from './components/session-list-item';
import Inspector from './inspector';

/**
 * Get the visual style (list or grid) from the block class name.
 *
 * @param {string} className
 *
 * @return {string} Either "grid" or "list".
 */
export function getStyle( className = '' ) {
	return className.includes( 'is-style-grid' ) ? 'grid' : 'list';
}

/**
 * Track Timetable Block: Edit
 *
 * @param {Object}                       props
 * @param {Object}                       props.attributes
 * @param {(attributes: Object) => void} props.setAttributes
 */
export default function Edit( { attributes, setAttributes } ) {
	const { track_id: trackId, className } = attributes;
	const style = getStyle( className );

	const blockProps = useBlockProps( {
		className: classnames( 'sessions', `is-style-${ style }` ),
	} );

	const { sessions, tracks, isLoading } = useSelect(
		( select ) => {
			const { getEntityRecords, hasFinishedResolution } =
				select( coreStore );

			const sessionsQuery = [
				'postType',
				'session',
				{ per_page: 100, track: trackId },
			];

			return {
				tracks: getEntityRecords( 'taxonomy', 'track', {
					per_page: 100,
				} ),
				sessions: trackId ? getEntityRecords( ...sessionsQuery ) : [],
				isLoading:
					!! trackId &&
					! hasFinishedResolution(
						'getEntityRecords',
						sessionsQuery
					),
			};
		},
		[ trackId ]
	);

	if ( ! trackId ) {
		const trackOptions = [
			{ value: 0, label: __( 'Select a track…', 'bm-event' ) },
			...( tracks || [] ).map( ( track ) => ( {
				value: track.id,
				label: track.name,
			} ) ),
		];

		return (
			<div { ...blockProps }>
				<Inspector
					attributes={ attributes }
					setAttributes={ setAttributes }
				/>
				<Placeholder
					icon="admin-post"
					label={ __( 'Select A Track', 'bm-event' ) }
					instructions={ __(
						'Select the track you want to display the timetable for.',
						'bm-event'
					) }
					isColumnLayout
				>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Select a track', 'bm-event' ) }
						onChange={ ( value ) =>
							setAttributes( { track_id: parseInt( value, 10 ) } )
						}
						options={ trackOptions }
					/>
				</Placeholder>
			</div>
		);
	}

	if ( isLoading ) {
		return (
			<div { ...blockProps }>
				<Inspector
					attributes={ attributes }
					setAttributes={ setAttributes }
				/>
				<Placeholder
					icon="admin-post"
					label={ __( 'Loading sessions…', 'bm-event' ) }
				>
					<Spinner />
				</Placeholder>
			</div>
		);
	}

	const Item = 'grid' === style ? SessionGridItem : SessionListItem;

	return (
		<>
			<Inspector
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<div { ...blockProps }>
				{ ( sessions || [] ).map( ( session ) => (
					<Item key={ session.id } data={ session } />
				) ) }
			</div>
		</>
	);
}

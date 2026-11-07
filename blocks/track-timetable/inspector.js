/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

/**
 * Track Timetable Block Inspector
 *
 * @param {Object}                       props
 * @param {Object}                       props.attributes
 * @param {(attributes: Object) => void} props.setAttributes
 */
export default function Inspector( { attributes, setAttributes } ) {
	const { showTimezoneSelector } = attributes;

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Timezone Selector', 'bm-event' ) }>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Show timezone selector?', 'bm-event' ) }
					checked={ showTimezoneSelector }
					onChange={ ( value ) =>
						setAttributes( { showTimezoneSelector: value } )
					}
				/>
			</PanelBody>
		</InspectorControls>
	);
}

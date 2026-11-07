/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import Edit from './edit';
import icon from './icon';

/**
 * The block is rendered in PHP (see render.php), so nothing is saved.
 */
registerBlockType( metadata.name, {
	icon,
	edit: Edit,
	save: () => null,
} );

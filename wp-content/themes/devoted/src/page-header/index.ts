import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit';
import './style.scss';

/**
 * Register the block using the metadata from block.json as the single source of
 * truth. This is a fully dynamic block — render.php produces both the
 * front-end markup and (via ServerSideRender in edit.tsx) the editor preview,
 * so `save` always returns null.
 */
registerBlockType(
	metadata.name,
	{ ...metadata, edit: Edit, save: () => null }
);

import { registerBlockType, type BlockConfiguration } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit';
import save from './save';
import './style.scss';

/**
 * Register the block using the metadata from block.json as the single source of
 * truth. The `name`, `title`, `category`, and `attributes` come from
 * block.json; `edit` and `save` are supplied here. The cast bridges the JSON's
 * widened types (e.g. `type: string`) to BlockConfiguration's stricter unions.
 */
registerBlockType(
	metadata.name,
	{ ...metadata, edit: Edit, save } as unknown as BlockConfiguration
);

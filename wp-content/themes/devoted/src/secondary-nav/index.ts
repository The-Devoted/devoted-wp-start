import { registerBlockType } from '@wordpress/blocks';
import { registerPlugin } from '@wordpress/plugins';

import metadata from './block.json';
import Edit from './edit';
import SecondaryNavSettingsPanel from './secondary-nav-settings-panel';
import './style.scss';

/**
 * Register the block using the metadata from block.json as the single source
 * of truth. This is a fully dynamic block — render.php produces both the
 * front-end markup and (via ServerSideRender in edit.tsx) the editor
 * preview, so `save` always returns null.
 */
registerBlockType(
	metadata.name,
	{ ...metadata, edit: Edit, save: () => null }
);

/**
 * Per-page secondary-nav settings (hide toggle, style override) live in the
 * Page's Document Settings sidebar, not this block's own Inspector — see
 * secondary-nav-settings-panel.tsx for why.
 */
registerPlugin( 'devoted-secondary-nav', { render: SecondaryNavSettingsPanel } );

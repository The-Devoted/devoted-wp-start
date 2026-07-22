import type { BlockVariation } from '@wordpress/blocks';

/**
 * Named presets of the block (shown in the inserter as separate entries).
 * Spread into the config passed to registerBlockType in index.ts.
 */
const variations: BlockVariation[] = [
	{
		name: 'example-greeting',
		title: 'Greeting',
		description: 'Example block pre-filled with a greeting message.',
		attributes: { message: 'Hello there!' },
	},
];

export default variations;

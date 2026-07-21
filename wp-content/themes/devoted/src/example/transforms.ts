import { createBlock } from '@wordpress/blocks';

/**
 * Converts to/from other block types. Spread into the config passed to
 * registerBlockType in index.ts.
 */
const transforms = {
	from: [
		{
			type: 'block' as const,
			blocks: ['core/paragraph'],
			transform: ( attributes: Record<string, unknown> ) =>
				createBlock( 'devoted/example', { message: attributes.content } ),
		},
	],
	to: [
		{
			type: 'block' as const,
			blocks: ['core/paragraph'],
			transform: ( attributes: Record<string, unknown> ) =>
				createBlock( 'core/paragraph', { content: attributes.message } ),
		},
	],
};

export default transforms;

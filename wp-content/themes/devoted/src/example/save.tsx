import { useBlockProps, RichText } from '@wordpress/block-editor';

interface SaveProps {
	attributes: {
		message: string;
	};
}

/**
 * The markup saved to post content and rendered on the front end.
 *
 * For a server-rendered (dynamic) block, return `null` here and add a
 * `render.php` referenced via `"render": "file:./render.php"` in block.json
 * instead.
 */
export default function save( { attributes }: SaveProps ) {
	const blockProps = useBlockProps.save();

	return (
		<RichText.Content
			{ ...blockProps }
			tagName="p"
			value={ `${attributes.message} lmao` }
		/>
	);
}

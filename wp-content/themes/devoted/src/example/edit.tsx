import { useBlockProps, RichText, Editblock } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

import "./editor.scss";

interface ExampleAttributes {
  message: string;
}

interface EditProps {
  attributes: ExampleAttributes;
  setAttributes: (attributes: Partial<ExampleAttributes>) => void;
}

/**
 * The editor-facing component. This is what an author interacts with inside the
 * block editor.
 */
export default function Edit({ attributes, setAttributes }: EditProps) {
  const blockProps = useBlockProps();

  return (
    <div>
      hello?
      <RichText
        {...blockProps}
        tagName="p"
        value={attributes.message}
        onChange={(message: string) => setAttributes({ message })}
        placeholder={__("Write a message…", "devoted")}
      />
    </div>
  );
}

import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";
import { __ } from "@wordpress/i18n";

import "./editor.scss";

type Mode = "trail" | "full";

interface SecondaryNavAttributes extends Record<string, unknown> {
  mode: Mode;
}

interface EditProps {
  attributes: SecondaryNavAttributes;
  setAttributes: (attributes: Partial<SecondaryNavAttributes>) => void;
}

const MODE_OPTIONS: { label: string; value: Mode }[] = [
  { label: __("Trail (root, ancestors, siblings)", "devoted"), value: "trail" },
  { label: __("Full tree", "devoted"), value: "full" },
];

/**
 * The editor-facing component. render.php owns all display logic — this
 * file only supplies Inspector controls and a ServerSideRender preview.
 * The preview depends on postId context, so it will only show real output
 * while editing a template in the context of an actual Page (e.g. via
 * "Show Template" from a Page), not while editing the template part alone.
 */
export default function Edit({ attributes, setAttributes }: EditProps) {
  const blockProps = useBlockProps();
  const { mode } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Secondary Navigation Settings", "devoted")}>
          <SelectControl
            label={__("Mode", "devoted")}
            value={mode}
            options={MODE_OPTIONS}
            onChange={(value: string) =>
              setAttributes({ mode: value as Mode })
            }
            help={__(
              "Set once per template — Trail shows the section root, a path through ancestors, and siblings. Full shows the entire section tree.",
              "devoted"
            )}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender
          block="devoted/secondary-nav"
          attributes={attributes}
        />
      </div>
    </>
  );
}

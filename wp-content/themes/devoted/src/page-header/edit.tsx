import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl, TextControl, ToggleControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";
import { __ } from "@wordpress/i18n";

import "./editor.scss";

type Variant = "page" | "post" | "cpt" | "archive" | "search" | "404";

interface PageHeaderAttributes extends Record<string, unknown> {
  variant: Variant;
  label: string;
  showExcerpt: boolean;
  separator: string;
}

interface EditProps {
  attributes: PageHeaderAttributes;
  setAttributes: (attributes: Partial<PageHeaderAttributes>) => void;
}

const VARIANT_OPTIONS: { label: string; value: Variant }[] = [
  { label: __("Page", "devoted"), value: "page" },
  { label: __("Post", "devoted"), value: "post" },
  { label: __("Custom post type", "devoted"), value: "cpt" },
  { label: __("Archive", "devoted"), value: "archive" },
  { label: __("Search", "devoted"), value: "search" },
  { label: __("404", "devoted"), value: "404" },
];

const LABEL_VARIANTS: Variant[] = ["post", "cpt", "search", "404"];

/**
 * The editor-facing component. render.php owns all display logic — this
 * file only supplies Inspector controls and a ServerSideRender preview, so
 * the editor and front end can never drift out of sync.
 */
export default function Edit({ attributes, setAttributes }: EditProps) {
  const blockProps = useBlockProps();
  const { variant, label, showExcerpt, separator } = attributes;

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Page Header Settings", "devoted")}>
          <SelectControl
            label={__("Variant", "devoted")}
            value={variant}
            options={VARIANT_OPTIONS}
            onChange={(value: string) =>
              setAttributes({ variant: value as Variant })
            }
            help={__(
              "Set once per template — determines how the title is derived.",
              "devoted"
            )}
          />
          {LABEL_VARIANTS.includes(variant) && (
            <TextControl
              label={__("Label", "devoted")}
              value={label}
              onChange={(value: string) => setAttributes({ label: value })}
              help={__("Fallback text shown for this variant.", "devoted")}
            />
          )}
          {variant === "page" && (
            <ToggleControl
              label={__("Show excerpt", "devoted")}
              checked={showExcerpt}
              onChange={(value: boolean) =>
                setAttributes({ showExcerpt: value })
              }
            />
          )}
          <TextControl
            label={__("Breadcrumb separator", "devoted")}
            value={separator}
            onChange={(value: string) => setAttributes({ separator: value })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <ServerSideRender block="devoted/page-header" attributes={attributes} />
      </div>
    </>
  );
}

import {
  useBlockProps,
  InspectorControls,
  InnerBlocks,
  store as blockEditorStore,
} from "@wordpress/block-editor";
import { createBlock } from "@wordpress/blocks";
import { useSelect, useDispatch } from "@wordpress/data";
import { useEntityProp } from "@wordpress/core-data";
import { store as editorStore } from "@wordpress/editor";
import { useEffect } from "@wordpress/element";
import { PanelBody, SelectControl, TextControl, ToggleControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";
import { __ } from "@wordpress/i18n";

// @wordpress/blocks doesn't export a usable template-array type in this
// project's installed type definitions, so we declare our own minimal shape
// for InnerBlocks' `template` prop: an array of [blockName, attributes?].
type InnerBlockTemplate = [string, Record<string, unknown>?];

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
  clientId: string;
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
 * The editor-facing component. render.php owns all display logic for every
 * variant except `page` — for that one, editing a real Page with "Show
 * Template" enabled needs to let editors click straight into the actual
 * title (and excerpt), which a ServerSideRender preview can't do, since
 * it's static rendered HTML rather than live blocks. So the `page` variant
 * instead renders a locked InnerBlocks shell around the real
 * core/post-title (and core/post-excerpt), which bind directly to the
 * current post's entity data — editing them there genuinely updates the
 * page's title/excerpt, same as editing it any other way. `templateLock:
 * "all"` prevents adding/removing/reordering blocks (keeping the header's
 * structure consistent across templates) without preventing text edits.
 *
 * Known limitation: changing `separator` or `showExcerpt` here after the
 * `page` variant's InnerBlocks have already been created won't retroactively
 * restructure them (Gutenberg only applies template changes to newly
 * inserted content) — the front end always reflects the current attributes
 * correctly regardless, since render.php reads them fresh on every request.
 *
 * Also for the `page` variant: publishing a brand-new Page (auto-draft →
 * publish) triggers Gutenberg to re-parse this block from the template's
 * saved content — which, since `save()` returns null, has no persisted
 * inner blocks — without re-running the `template` prop's usual
 * auto-population, leaving it correctly present but with zero children
 * (invisible) until the editor is reloaded. The effect below detects that
 * (page variant, no children) and rebuilds them itself, so it self-heals
 * within the same session instead of requiring a manual reload.
 *
 * core/post-excerpt falls back to an auto-generated excerpt (trimmed post
 * content) when a page has no manual excerpt, which render.php's own
 * has_excerpt() check on the front end never shows — to match that in the
 * editor, the `dvo--page-header--has-excerpt` class is only added once the
 * post's raw excerpt is non-empty, and editor.scss hides the post-excerpt
 * block entirely unless that class is present.
 */
export default function Edit({ attributes, setAttributes, clientId }: EditProps) {
  const { variant, label, showExcerpt, separator } = attributes;

  const { currentPostType, currentPostId } = useSelect((select) => ({
    currentPostType: select(editorStore).getCurrentPostType(),
    currentPostId: select(editorStore).getCurrentPostId(),
  }));
  const [excerptRaw] = useEntityProp(
    "postType",
    currentPostType,
    "excerpt",
    currentPostId ?? undefined
  );
  const hasManualExcerpt =
    typeof excerptRaw === "string" && excerptRaw.trim().length > 0;

  // Mirrors the class list render.php passes to get_block_wrapper_attributes()
  // — useBlockProps() only auto-applies the generic block-supports classes,
  // not these custom ones, so without this the editor's outer wrapper is
  // missing the constrained-width/padding treatment the front end has.
  const blockProps = useBlockProps({
    className: [
      "dvo--page-header",
      `dvo--page-header--${variant}`,
      "wp-block-group",
      "has-global-padding",
      "is-layout-constrained",
      "wp-block-group-is-layout-constrained",
      hasManualExcerpt && "dvo--page-header--has-excerpt",
    ]
      .filter(Boolean)
      .join(" "),
  });

  const pageTemplate: InnerBlockTemplate[] = [
    ["core/breadcrumbs", { separator, showCurrentItem: true }],
    ["core/post-title", { level: 1 }],
    ...(showExcerpt ? ([["core/post-excerpt"]] as InnerBlockTemplate[]) : []),
  ];

  const innerBlockCount = useSelect(
    (select) => select(blockEditorStore).getBlockOrder(clientId).length,
    [clientId]
  );
  const { replaceInnerBlocks } = useDispatch(blockEditorStore);

  useEffect(() => {
    if (variant !== "page" || innerBlockCount > 0) {
      return;
    }
    replaceInnerBlocks(
      clientId,
      [
        createBlock("core/breadcrumbs", { separator, showCurrentItem: true }),
        createBlock("core/post-title", { level: 1 }),
        ...(showExcerpt ? [createBlock("core/post-excerpt")] : []),
      ],
      false
    );
    // Re-run only when the emptiness (re)appears, not on every keystroke —
    // separator/showExcerpt are read fresh from the closure when it does.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [variant, innerBlockCount, clientId]);

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
        {variant === "page" ? (
          // render.php wraps its content in this same "alignwide" inner
          // group — without it here, the InnerBlocks content renders at
          // full constrained width instead of the narrower aligned column.
          <div className="wp-block-group alignwide is-layout-flow wp-block-group-is-layout-flow">
            <InnerBlocks template={pageTemplate} templateLock="all" />
          </div>
        ) : (
          <ServerSideRender
            block="devoted/page-header"
            attributes={attributes}
          />
        )}
      </div>
    </>
  );
}

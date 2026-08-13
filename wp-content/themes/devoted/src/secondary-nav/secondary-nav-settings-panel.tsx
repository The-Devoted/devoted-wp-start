import { PluginDocumentSettingPanel } from "@wordpress/editor";
import { useEntityProp } from "@wordpress/core-data";
import { useSelect } from "@wordpress/data";
import { store as editorStore } from "@wordpress/editor";
import { ToggleControl, SelectControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

const HIDE_KEY = "devoted_hide_from_secondary_nav";
const MODE_KEY = "devoted_secondary_nav_mode";

const MODE_OPTIONS = [
  { label: __("Use Template Default", "devoted"), value: "" },
  { label: __("Trail", "devoted"), value: "trail" },
  { label: __("Full Tree", "devoted"), value: "full" },
];

/**
 * Per-page secondary-nav settings, registered in the Page's Document
 * Settings sidebar rather than the devoted/secondary-nav block's own
 * Inspector — the block instance lives in parts/sidebar-page.html, outside
 * the editable post-content area, so it isn't selectable from the Pages
 * editor and can't host its own per-instance controls for this page.
 */
export default function SecondaryNavSettingsPanel() {
  const { postType, postId } = useSelect((select) => ({
    postType: select(editorStore).getCurrentPostType(),
    postId: select(editorStore).getCurrentPostId(),
  }));

  // useEntityProp's third argument must be "meta" (the whole meta object) —
  // post meta fields are nested under record.meta, not top-level entity
  // properties, so passing a specific meta key name directly here would
  // always read/write undefined regardless of the page's actual value.
  // Passing postId explicitly (rather than relying on an ancestor
  // EntityProvider's context, which content rendered into a
  // PluginDocumentSettingPanel isn't guaranteed to be inside) avoids
  // silently targeting a phantom record.
  const [meta, setMeta] = useEntityProp(
    "postType",
    postType,
    "meta",
    postId ?? undefined
  );

  if (postType !== "page") {
    return null;
  }

  const metaValues = (meta ?? {}) as Record<string, unknown>;
  const isHidden = !!metaValues[HIDE_KEY];
  const modeOverride = (metaValues[MODE_KEY] as string) ?? "";

  return (
    <PluginDocumentSettingPanel
      name="devoted-secondary-nav"
      title={__("Secondary Navigation", "devoted")}
    >
      <div className="dvo--secondary-nav-panel">
        <SelectControl
          label={__("Secondary Nav Contents", "devoted")}
          value={modeOverride}
          options={MODE_OPTIONS}
          onChange={(value: string) =>
            setMeta({ ...metaValues, [MODE_KEY]: value })
          }
          help={__(
            "Option to override this template's default secondary nav behavior.",
            "devoted"
          )}
        />
        <ToggleControl
          label={__(
            "Hide this page from the secondary navigation",
            "devoted"
          )}
          checked={isHidden}
          onChange={(value: boolean) =>
            setMeta({ ...metaValues, [HIDE_KEY]: value })
          }
        />
      </div>
    </PluginDocumentSettingPanel>
  );
}

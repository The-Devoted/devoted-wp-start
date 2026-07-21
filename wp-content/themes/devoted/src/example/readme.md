# Example block

Reference block. Copy this folder to start a new block, then trim what you
don't need.

| File | Purpose | Wired up here? |
| --- | --- | --- |
| `block.json` | Block metadata: name, attributes, supports, asset references. | Yes |
| `index.ts` | Registers the block, importing everything else. | Yes |
| `edit.tsx` | Editor-facing component. | Yes |
| `save.tsx` | Static markup saved to post content and rendered on the front end. | Yes |
| `editor.scss` | Editor-only styles. | Yes |
| `style.scss` | Styles for both editor and front end. | Yes |
| `view.ts` | Frontend-only script (no editor overhead). Referenced via `viewScript` in block.json. | Yes |
| `transforms.ts` | Converts to/from other block types (e.g. `core/paragraph`). Spread into `registerBlockType`. | Yes |
| `variations.ts` | Named presets of the block shown in the inserter. Spread into `registerBlockType`. | Yes |
| `render.php` | Server-side render callback for a *dynamic* block. | No — mutually exclusive with `save.tsx`; see the comment in that file. |

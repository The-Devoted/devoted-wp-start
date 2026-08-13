# Secondary Navigation block

Replaces the old `devoted/site-secondary-nav` pattern. All rendering logic
lives in `render.php`; `edit.tsx` only supplies Inspector controls and a
`ServerSideRender` preview.

## Attributes

| Attribute | Purpose |
| --- | --- |
| `mode` | `trail` (default) or `full`. Set once per template — the site-wide default. |

- **`trail`**: a link to the section root, a single-link path through each
  intermediate ancestor, then the current page's siblings (current page
  highlighted).
- **`full`**: a link to the section root, then the entire nested tree under
  it (current page highlighted wherever it falls).

`mode` is set in exactly two places, no others: the template attribute
above, and the "Secondary nav style" dropdown in the Document Settings
panel described below (`devoted_secondary_nav_mode` post meta), which
`render.php` checks only on the exact page currently being viewed and
which takes precedence over the template attribute when set. There is no
fallback to a section's root page or any other ancestor — a page's nav
style is either declared by the template or set explicitly on that page,
never inherited implicitly.

Unlike the old pattern (which used `core/page-list` and had to print the
root link as a separate paragraph outside the generated list), the root
link here is a real `<li>` inside the same `<ul>` as the rest of the nav,
with the rest nested inside it — one coherent list, at any depth.

## Hiding a page from the nav

Editing a Page shows a "Secondary Navigation" panel in the Document
Settings sidebar (not this block's own Inspector — the block instance
lives in `parts/sidebar-page.html`, outside the editable post-content area,
so it isn't reachable from the Page's own block selection). Toggling
"Hide this page from the secondary navigation" sets the
`devoted_hide_from_secondary_nav` post meta, which excludes that page (and
its descendants) from sibling/tree listings everywhere. The section root
link and the trail's ancestor path are never affected by this toggle, since
they exist to let a visitor navigate back up regardless.

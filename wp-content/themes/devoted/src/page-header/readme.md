# Page Header block

Site-wide dynamic page header. Replaces the old `devoted/site-page-header`
pattern. All rendering logic lives in `render.php`; `edit.tsx` only supplies
Inspector controls and a `ServerSideRender` preview (no hand-duplicated
logic in JS).

## Attributes

| Attribute | Purpose |
| --- | --- |
| `variant` | `page` \| `post` \| `cpt` \| `archive` \| `search` \| `404`. Set once per template — determines how the title/label is derived. Not meant for per-page editing. |
| `label` | Fallback/override text for the `post`, `cpt`, `search`, and `404` variants (e.g. "News", "Search Results"). Set once per template. |
| `showExcerpt` | `page` variant only — whether to show the post excerpt below the title, if one exists. |
| `separator` | Breadcrumb separator character, default `>`. |

## Variant → template mapping

| Template | variant |
| --- | --- |
| `page.html`, `page-no-sidebar.html`, `page-landing.html` | `page` |
| `single.html`, `single-no-sidebar.html`, `index.html` | `post` |
| `category.html`, `tag.html`, `archive.html` | `archive` |
| `search.html` | `search` |
| `404.html` | `404` |

## Styles

Two block styles are registered: `default` and `alternate`. No CSS ships for
`alternate` yet — it's wired up (selectable in the editor, reflected
correctly in both the `ServerSideRender` preview and the front end) so
visual treatment can be authored later without further block changes.

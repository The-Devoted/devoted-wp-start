# E2E Tests

Playwright (TypeScript) tests for the Devoted WordPress starter kit.

Tests are a separate npm package from `wp-content/themes/devoted`.

## Tests & Scope

No tests perform logins to the site. Only publicly-accessible pages are tested.

### Smoke Test

`specs/smoke.spec.ts` verifies that the homepage renders for an anonymous visitor.

### Accessibility Scan

`specs/accessibility.spec.ts` runs an
  [axe-core](https://www.npmjs.com/package/@axe-core/playwright) WCAG 2.0/2.1
  A/AA scan. When a violation is found, it's added to a report and testing
  continues until all required pages are scanned.
  
The following pages/templates are tested:

- Homepage
- Any pages linked in the Main Navigation. See [Navigation](#navigation).
- Structural templates such as:
  - Single Posts
  - Taxonomy Archives
  - Search Results
  - 404
- An optional manual list of pages. See [Structural Pages](#structural-pages).

The following are **not** tested:

- A full sitemap of content.
  - Reasoning: this test suite's job is to test _theme code_, not _site
    content_. Content should be scanned with an accessibility checking plugin
    that provides easily-understandable feedback to the content editor. This
    provides a non-technical path to help ensure accessibility compliance after
    our engagement has ended.
- The login page or admin area.
  - Reasoning: we typically encourage clients to employ a login security plugin
    to reroute their admin URLs. This gives them the freedom to adjust the URL
    as needed (or revert to `wp-admin` should they choose to). The admin URL is
    not a stable target. Also, as we want to rely on native WP functionality as
    much as possible, there are limited scenarios where testing operations
    behind the login are necessary in the first place.

## Page Objects

### Pages

- Location: `pages/`
- One class per discrete screen (`FrontEndPage`).
- Each exposes locators and actions for that screen; specs compose them and keep the
  `expect()` assertions.

## Fixtures

### WP-CLI

- Location: `fixtures/wpCli.ts`
- Exported function: `wp(args)`
- Runs a `wp` CLI command inside the WordPress container.
- Used by `global-setup.ts` to bootstrap the environment, and by
  `fixtures/structuralPages.ts`.

### Accessibility

- Location: `fixtures/accessibility.ts`
- Exported functions:
  - `expectNoAccessibilityViolations(page, exclude?)`
    - A thin wrapper around `AxeBuilder` used by `accessibility.spec.ts`.
    - The `exclude` param takes CSS selectors for regions to skip.
  - `getAccessibilityViolations` returns violations instead of asserting on them,
  so a caller can keep going instead of stopping at the first bad page.
  - `summarizeAccessibilityViolations` turns one page’s violations into readable
  text. The spec then collects one of these per URL and joins them into the
  single final report.

### Navigation

- Location: `fixtures/navigation.ts`
- Exported function: `getPrimaryNavUrls(page)`
- Identifies the site's main navigation via the selector `header.site-header nav a[href]`.
- Reads the internal links out of that identified main nav.

### Structural Pages

- Location: `fixtures/structuralPages.ts`
- Exported function: `getStructuralScanUrls()`
- Resolves live via wp-cli, so the accessibility scan reaches structural page templates.
  - See [Accessibility Scan](#accessibility-scan) for a full list.
- Anything it can't resolve is left out. For example, a taxonomy with no terms in use yet.
- `MANUAL_SCAN_PATHS` allows us to pass a list of additional paths to test.

## Known gap: forked PRs fall back to the free ACF plugin

The theme needs ACF's `get_field()` or the site 500s. Production/local dev
use ACF Pro via Composer + a WP Engine license (see root README). CI has
those credentials in the `COMPOSER_AUTH_JSON` repo secret and runs
`composer update` for same-repo PRs and pushes to `main`. Forked PRs don't
get repo secrets, so `global-setup.ts` falls back to installing the free ACF
plugin when nothing providing `get_field()` is active. This suite doesn't
assert on ACF-specific behavior, so the fallback is fine for smoke coverage.

## Running locally

1. `docker compose up -d --build` from the repo root.
2. `npm install` (first time only).
3. `npm test`.

`global-setup.ts` waits for WordPress to be ready, then checks whether it's
already installed.

If it isn't, we assume a brand-new scratch container. It runs `wp core install`
with fixed test credentials, then bootstraps a working site:

- Activates the `devoted` theme and every installed plugin.
- Ensures ACF is available.

If WordPress is already installed, none of that bootstrapping runs — see [Running Against a Real Environment](#running-against-a-real-environment).

| Var | Default | Purpose |
|---|---|---|
| `WP_BASE_URL` | `http://localhost:8000` | Site under test |
| `WP_ADMIN_USER` | `admin` | Admin username `wp core install` bootstraps a scratch install with |
| `WP_ADMIN_PASSWORD` | `password` | Admin password `wp core install` bootstraps a scratch install with |
| `WP_CONTAINER_NAME` | `wordpress` | Docker container `wp exec` runs against |

`WP_ADMIN_USER`/`WP_ADMIN_PASSWORD` only matter for a brand-new scratch
install — no spec logs in, so they're otherwise unused.

## Running Against a Real Environment

`WP_BASE_URL`/`WP_CONTAINER_NAME` can point this suite at any local environment where WordPress is already installed instead of a fresh scratch container.

`global-setup.ts` tells the difference by checking `wp core is-installed`:

- If it is, we assume an existing environment. No changes to the site are made so the exact environment is tested.
- If it's not, we assume a fresh install and a working site is bootstrapped.

Other commands:

- `npm run test:ui` — Playwright UI mode, for writing/debugging specs.
- `npm run report` — open the last HTML report.
- `npm run typecheck` — type-check specs (not run by `npm test`).

## Running in CI

`.github/workflows/test.yml` runs on PRs and pushes to `main`:

- Writes a throwaway `.env`/`auth.json`
- Starts the Docker stack
- Builds the theme's blocks
- Runs `npm test` against Chromium only.
- On failure, uploads the Playwright HTML report (with traces) as a build artifact.

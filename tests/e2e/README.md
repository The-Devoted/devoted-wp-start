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
  A/AA scan of the homepage.

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
- Used by `global-setup.ts` to bootstrap the environment.

### Accessibility

- Location: `fixtures/accessibility.ts`
- Exported function: `expectNoAccessibilityViolations(page, exclude?)`
- A thin wrapper around `AxeBuilder` used by `accessibility.spec.ts`.
- The `exclude` param takes CSS selectors for regions to skip.

## Running locally

1. `docker compose up -d --build` from the repo root.
2. `npm install` (first time only).
3. `npm test`.

`global-setup.ts` waits for WordPress to be ready, then checks whether it's
already installed.

If it isn't, we assume a brand-new scratch container. It runs `wp core install`
with fixed test credentials, then bootstraps a working site:

- Activates the `devoted` theme and every installed plugin.

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

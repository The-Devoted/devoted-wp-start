# E2E Tests

Playwright (TypeScript) smoke tests for the Devoted WordPress starter kit —
catch environment regressions (plugin updates, WP core bumps, dependency
changes) before they hit production. Separate npm package from
`wp-content/themes/devoted`.

## Scope

Small slice, core smoke coverage only:

- `specs/auth.setup.ts` — logs in as the test admin once; other specs reuse
  that session via Playwright's `storageState`.
- `specs/smoke.spec.ts` — front page renders for an anonymous visitor, admin
  dashboard loads after login.
- `specs/editor.spec.ts` — creates a page with a core Paragraph block and the
  theme's `devoted/example` block, publishes it, checks both render.
- `specs/accessibility.spec.ts` — runs an [axe-core](https://www.npmjs.com/package/@axe-core/playwright)
  WCAG 2.0/2.1 A/AA scan against the front page, login page, and admin
  dashboard, failing on any violation.

## Page objects

Specs drive the site through page objects rather than raw locators, so
implementation is repeatable and the suite's vocabulary matches the app's:

- `pages/` — one class per discrete screen (`LoginPage`, `DashboardPage`,
  `FrontEndPage`, `BlockEditorPage`). Each exposes locators and actions for
  that screen; specs compose them and keep the `expect()` assertions.
- `components/` — reusable pieces embedded in more than one page
  (`AdminBar`, `PublishPanel`).
- `fixtures/auth.ts` — a scenario-level helper (`loginAsAdmin`) built from
  `LoginPage`, for the "log in as the standard test admin" workflow used by
  `auth.setup.ts`.
- `fixtures/accessibility.ts` — `expectNoAccessibilityViolations(page, exclude?)`,
  a thin wrapper around `AxeBuilder` used by `accessibility.spec.ts`. The
  `exclude` param takes CSS selectors for third-party plugin markup this
  suite doesn't own (see "Known gap" below), not theme/core chrome.

Locator and method names mirror Gutenberg/WordPress's own accessible names
("Add title", "Add default block", "Editor publish") instead of inventing
parallel terminology, so the code stays legible against the actual UI.

Adding coverage for a new screen or component should mean adding a class
under `pages/` or `components/`, not new inline locators in a spec.

Per-plugin checks (ACF fields, Yoast/Rank Math, login-security, cookie
consent, etc.) are out of scope for now.

## Known gap: dashboard a11y scan excludes the Accessibility Checker widget

The Accessibility Checker plugin's own dashboard summary widget
(`#edac_dashboard_scan_summary`) renders a progressbar that axe flags
(missing accessible name, invalid `aria-valuenow="N/A"`) — a bug in that
plugin, not the theme, so `accessibility.spec.ts` excludes it rather than
failing CI on something this repo can't fix. Update the selector if the
plugin's markup changes, or drop the exclusion once it's fixed upstream.

## Known gap: forked PRs fall back to the free ACF plugin

The theme needs ACF's `get_field()` or the site 500s. Production/local dev
use ACF Pro via Composer + a WP Engine license (see root README). CI has
those credentials in the `COMPOSER_AUTH_JSON` repo secret and runs
`composer update` for same-repo PRs and pushes to `main`. Forked PRs don't
get repo secrets, so `global-setup.ts` falls back to installing the free ACF
plugin when nothing providing `get_field()` is active. This suite doesn't
assert on ACF-specific behavior, so the fallback is fine for smoke coverage
— it just blocks per-plugin specs from running against forked PRs.

## Running locally

1. `docker compose up -d --build` from the repo root.
2. `npm install` (first time only).
3. `npm test`.

`global-setup.ts` waits for WordPress to be ready, runs an idempotent
`wp core install` with fixed test credentials, activates the `devoted` theme
and installed plugins, and ensures ACF is available.

| Var | Default | Purpose |
|---|---|---|
| `WP_BASE_URL` | `http://localhost:8000` | Site under test |
| `WP_ADMIN_USER` | `admin` | Test admin username |
| `WP_ADMIN_PASSWORD` | `password` | Test admin password |
| `WP_CONTAINER_NAME` | `wordpress` | Docker container `wp exec` runs against |

Other commands:

- `npm run test:ui` — Playwright UI mode, for writing/debugging specs.
- `npm run report` — open the last HTML report.
- `npm run typecheck` — type-check specs (not run by `npm test`).

## Running in CI

`.github/workflows/test.yml` runs on PRs and pushes to `main`: writes a
throwaway `.env`/`auth.json`, brings up the Docker stack, builds the theme's
blocks, then runs `npm test` against Chromium only. On failure, uploads the
Playwright HTML report (with traces) as a build artifact.

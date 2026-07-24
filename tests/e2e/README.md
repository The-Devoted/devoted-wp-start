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

Locator and method names mirror Gutenberg/WordPress's own accessible names
("Add title", "Add default block", "Editor publish") instead of inventing
parallel terminology, so the code stays legible against the actual UI.

Adding coverage for a new screen or component should mean adding a class
under `pages/` or `components/`, not new inline locators in a spec.

Per-plugin checks (ACF fields, Yoast/Rank Math, login-security, cookie
consent, etc.) are out of scope for now.

## Known gap: ACF is stood in, not the real dependency

The theme needs ACF's `get_field()` or the site 500s. Production/local dev
use ACF Pro via Composer + a WP Engine license (see root README). CI doesn't
have those credentials yet, so `global-setup.ts` falls back to installing the
free ACF plugin when nothing providing `get_field()` is active. Wiring up the
real Composer credentials would remove this fallback and unblock per-plugin
specs.

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

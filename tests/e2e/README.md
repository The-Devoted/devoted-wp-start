# E2E Tests

Playwright (TypeScript) smoke tests for the Devoted WordPress starter kit —
catch environment regressions (plugin updates, WP core bumps, dependency
changes) before they hit production. Separate npm package from
`wp-content/themes/devoted`.

## Scope

Small slice, core smoke coverage only, against anonymously accessible pages
— no spec logs in or exercises wp-admin:

- `specs/smoke.spec.ts` — front page renders for an anonymous visitor.
- `specs/accessibility.spec.ts` — runs an [axe-core](https://www.npmjs.com/package/@axe-core/playwright)
  WCAG 2.0/2.1 A/AA scan against the front page, failing on any violation. It
  also crawls every URL listed in the site's sitemap (`fixtures/sitemap.ts`)
  and scans each one, reporting every offending URL and its violations
  together rather than failing on the first bad page. The login page isn't
  covered here — a login-security plugin can move it off a fixed URL, so
  it's not a stable target for this suite.

## Page objects

Specs drive the site through page objects rather than raw locators, so
implementation is repeatable and the suite's vocabulary matches the app's:

- `pages/` — one class per discrete screen (`FrontEndPage`). Each exposes
  locators and actions for that screen; specs compose them and keep the
  `expect()` assertions.
- `fixtures/accessibility.ts` — `expectNoAccessibilityViolations(page, exclude?)`,
  a thin wrapper around `AxeBuilder` used by `accessibility.spec.ts`. The
  `exclude` param takes CSS selectors for third-party plugin markup this
  suite doesn't own (see "Known gap" below), not theme/core chrome.
  `getAccessibilityViolations`/`summarizeAccessibilityViolations` expose the
  same scan without asserting, for callers (the sitemap crawl) that need to
  keep going across several pages and report everything at once.
- `fixtures/sitemap.ts` — `fetchSitemapUrls(indexUrl?)` walks a sitemap
  index (Yoast SEO's `/sitemap_index.xml` by default) and returns every page
  URL it lists, recursing into nested per-post-type sitemaps.
- `fixtures/wpCli.ts` — `wp(args)`, runs a `wp` CLI command inside the
  WordPress container. Used by `global-setup.ts` to bootstrap the environment.

Locator and method names mirror WordPress's own accessible names instead of
inventing parallel terminology, so the code stays legible against the
actual UI.

Adding coverage for a new anonymously accessible screen should mean adding a
class under `pages/`, not new inline locators in a spec. This suite doesn't
cover wp-admin or any screen that requires being logged in — see "Scope"
above.

Per-plugin checks (ACF fields, Yoast/Rank Math, login-security, cookie
consent, etc.) are out of scope for now.

## Known gap: sitemap a11y crawl skips the bundled "Website User Guide"

`/website-user-guide/` and its subpages are onboarding documentation that
ships with the starter kit, not theme or environment code — their demo
content has its own pre-existing issues (a `<marquee>`, low-contrast example
text, a non-focusable scrollable region) that are content to fix, not
something this suite should gate CI on. `accessibility.spec.ts` filters any
sitemap URL containing `/website-user-guide/` out of the crawl. Drop the
filter if that content gets cleaned up, or narrow it further if only some
subpages are fixed.

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

`global-setup.ts` waits for WordPress to be ready, then checks whether it's
installed yet. If it isn't — a brand-new scratch container — it runs
`wp core install` with fixed test credentials and bootstraps a working site:
switches permalinks to `/%postname%/` (a fresh install defaults to plain
`?p=123` permalinks, under which Yoast SEO's sitemap URLs 404), activates
the `devoted` theme and installed plugins, and ensures ACF is available. If
WordPress is already installed, none of that bootstrapping runs — see
"Running against a pulled/real environment" below.

| Var | Default | Purpose |
|---|---|---|
| `WP_BASE_URL` | `http://localhost:8000` | Site under test |
| `WP_ADMIN_USER` | `admin` | Admin username `wp core install` bootstraps a scratch install with |
| `WP_ADMIN_PASSWORD` | `password` | Admin password `wp core install` bootstraps a scratch install with |
| `WP_CONTAINER_NAME` | `wordpress` | Docker container `wp exec` runs against |

`WP_ADMIN_USER`/`WP_ADMIN_PASSWORD` only matter for a brand-new scratch
install — no spec logs in, so they're otherwise unused.

## Running against a pulled/real environment

`WP_BASE_URL`/`WP_CONTAINER_NAME` can point this suite at a local
environment refreshed from a remote one with `db_utils.sh` (see root
README) instead of a scratch container. The goal there is to test that
environment's truth, not adulterate it to make tests pass — so
`global-setup.ts` only bootstraps theme/plugin activation, permalinks, and
the ACF fallback for a container it installs WordPress into itself. Once
`wp core is-installed` is already true (as it will be right after a DB
pull — active theme, active plugins, and permalink structure all live in
the database `db_utils.sh` exports), setup skips all of that and leaves the
pulled config exactly as it is.

Other commands:

- `npm run test:ui` — Playwright UI mode, for writing/debugging specs.
- `npm run report` — open the last HTML report.
- `npm run typecheck` — type-check specs (not run by `npm test`).

## Running in CI

`.github/workflows/test.yml` runs on PRs and pushes to `main`: writes a
throwaway `.env`/`auth.json`, brings up the Docker stack, builds the theme's
blocks, then runs `npm test` against Chromium only. On failure, uploads the
Playwright HTML report (with traces) as a build artifact.

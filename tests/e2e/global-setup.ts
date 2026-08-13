import { ADMIN_PASSWORD, ADMIN_USER, BASE_URL } from './constants';
import { wp } from './fixtures/wpCli';

const MAX_WAIT_MS = 60_000;
const POLL_INTERVAL_MS = 2_000;

function isInstalled(): boolean {
  try {
    wp(['core', 'is-installed']);
    return true;
  } catch {
    return false;
  }
}

/**
 * Installs WordPress with fixed test credentials, retrying until it succeeds.
 * On a first boot the WordPress container can answer `wp` commands before
 * MariaDB has finished initializing, so `wp core install` (and even `wp core
 * is-installed`) can fail transiently — this just keeps trying rather than
 * probing readiness a separate way.
 *
 * Returns whether this call actually created the install (`true`) or found
 * one already there (`false`). An already-installed site may hold real
 * plugin/theme/permalink config pulled from a remote environment (see
 * `db_utils.sh` in the repo root) — the rest of setup uses this to decide
 * whether it's safe to bootstrap defaults or whether doing so would
 * overwrite that config.
 */
async function ensureWordPressInstalled(): Promise<boolean> {
  const deadline = Date.now() + MAX_WAIT_MS;
  let lastError: unknown;

  while (Date.now() < deadline) {
    if (isInstalled()) return false;

    try {
      const { origin } = new URL(BASE_URL);
      wp([
        'core',
        'install',
        `--url=${origin}`,
        '--title=Devoted WP Start',
        `--admin_user=${ADMIN_USER}`,
        `--admin_password=${ADMIN_PASSWORD}`,
        '--admin_email=e2e@example.com',
        '--skip-email',
      ]);
      return true;
    } catch (error) {
      lastError = error;
      await new Promise((resolve) => setTimeout(resolve, POLL_INTERVAL_MS));
    }
  }

  throw new Error(
    `Timed out after ${MAX_WAIT_MS}ms waiting for WordPress to install at ${BASE_URL}. ` +
      `Is the Docker stack running (\`docker compose up -d --build\`)? Last error: ${lastError}`
  );
}

function hasPrettyPermalinks(): boolean {
  return wp(['option', 'get', 'permalink_structure']).trim().length > 0;
}

/**
 * A fresh `wp core install` defaults to plain `?p=123` permalinks, under
 * which Yoast SEO's pretty sitemap URLs (`/post-sitemap.xml`, etc.) 404 —
 * only their `?sitemap=` query-var form resolves. Pretty permalinks are
 * also what real sites run, so this fixes the environment rather than
 * teaching the sitemap fixture to route around plain permalinks. Only
 * called for an install this run just created (see `globalSetup`) — an
 * existing site's permalink structure is real config, not ours to change.
 */
function ensurePrettyPermalinks(): void {
  if (hasPrettyPermalinks()) return;
  wp(['rewrite', 'structure', '/%postname%/', '--hard']);
}

function hasActiveAcf(): boolean {
  const active = wp(['plugin', 'list', '--status=active', '--field=name']);
  return active
    .split('\n')
    .map((name) => name.trim())
    .some((name) => name.startsWith('advanced-custom-fields'));
}

/**
 * The theme's patterns call ACF's `get_field()`, so without ACF active the
 * site 500s outright. Production, local dev, and same-repo CI runs use ACF
 * Pro via Composer + a WP Engine license (see the README); forked PRs don't
 * get repo secrets, so fall back to the free version from wordpress.org just
 * so the site is up for these smoke tests. This suite doesn't assert on
 * ACF-specific behavior — see the specs README — it just needs *a* working
 * site. Only called for an install this run just created (see
 * `globalSetup`).
 */
function ensureAcfAvailable(): void {
  if (hasActiveAcf()) return;
  wp(['plugin', 'install', 'advanced-custom-fields', '--activate']);
}

/**
 * Bootstraps a brand-new scratch WordPress install so the suite has
 * something working to run against: turns on pretty permalinks, activates
 * the theme and every installed plugin, and falls back to free ACF if
 * nothing else provides it.
 *
 * Only called when `ensureWordPressInstalled` just created the install —
 * an already-installed site (e.g. one refreshed via `db_utils.sh` from a
 * remote environment) already holds real plugin/theme/permalink config,
 * and this suite exists to test that environment's truth, not overwrite it
 * to make tests pass.
 */
function bootstrapFreshInstall(): void {
  ensurePrettyPermalinks();
  wp(['theme', 'activate', 'devoted']);
  wp(['plugin', 'activate', '--all']);
  ensureAcfAvailable();
}

export default async function globalSetup(): Promise<void> {
  const freshlyInstalled = await ensureWordPressInstalled();
  if (freshlyInstalled) {
    bootstrapFreshInstall();
  }
}

import { execFileSync } from 'node:child_process';
import { ADMIN_PASSWORD, ADMIN_USER, BASE_URL, WP_CONTAINER_NAME } from './constants';

const MAX_WAIT_MS = 60_000;
const POLL_INTERVAL_MS = 2_000;

function wp(args: string[]): string {
  return execFileSync('docker', ['exec', '-u', 'www-data', WP_CONTAINER_NAME, 'wp', ...args], {
    encoding: 'utf-8',
    stdio: ['ignore', 'pipe', 'ignore'],
  });
}

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
 */
async function ensureWordPressInstalled(): Promise<void> {
  const deadline = Date.now() + MAX_WAIT_MS;
  let lastError: unknown;

  while (Date.now() < deadline) {
    if (isInstalled()) return;

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
      return;
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
 * site.
 */
function ensureAcfAvailable(): void {
  if (hasActiveAcf()) return;
  wp(['plugin', 'install', 'advanced-custom-fields', '--activate']);
}

export default async function globalSetup(): Promise<void> {
  await ensureWordPressInstalled();

  wp(['theme', 'activate', 'devoted']);
  wp(['plugin', 'activate', '--all']);
  ensureAcfAvailable();
}

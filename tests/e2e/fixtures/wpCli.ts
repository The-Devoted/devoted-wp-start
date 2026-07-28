import { execFileSync } from 'node:child_process';
import { WP_CONTAINER_NAME } from '../constants';

/**
 * Runs a `wp` CLI command inside the WordPress container. Shared by
 * `global-setup.ts` (environment bootstrap) and `fixtures/wpPosts.ts` (test
 * asset setup/teardown) so both go through the same docker exec plumbing.
 */
export function wp(args: string[]): string {
  return execFileSync('docker', ['exec', '-u', 'www-data', WP_CONTAINER_NAME, 'wp', ...args], {
    encoding: 'utf-8',
    stdio: ['ignore', 'pipe', 'ignore'],
  });
}

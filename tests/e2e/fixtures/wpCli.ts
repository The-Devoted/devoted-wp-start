import { execFileSync } from 'node:child_process';
import { WP_CONTAINER_NAME } from '../constants';

/**
 * Runs a `wp` CLI command inside the WordPress container. Used by
 * `global-setup.ts` to bootstrap the environment.
 */
export function wp(args: string[]): string {
  return execFileSync('docker', ['exec', '-u', 'www-data', WP_CONTAINER_NAME, 'wp', ...args], {
    encoding: 'utf-8',
    stdio: ['ignore', 'pipe', 'ignore'],
  });
}

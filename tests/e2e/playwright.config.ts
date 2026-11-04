import { existsSync } from 'node:fs';
import path from 'node:path';
import { defineConfig, devices } from '@playwright/test';

// Local env overrides (optional):
// - Loads tests/e2e/.env (gitignored) before `./constants` reads process.env.
// - Lets WP_ADMIN_USER/WP_ADMIN_PASSWORD/etc. be set per machine instead of
//   exported in the shell.
// - No-op in CI: those vars are set directly in the environment there, and
//   no .env file is checked in.
const dotenvPath = path.join(__dirname, '.env');
if (existsSync(dotenvPath)) {
  process.loadEnvFile(dotenvPath);
}

import { BASE_URL } from './constants';

export default defineConfig({
  testDir: './specs',
  outputDir: './test-results',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  // No workers override:
  // It's safe to run these concurrently since the specs only read (no login or data mutation).
  reporter: process.env.CI ? [['html', { open: 'never' }], ['list']] : 'list',
  globalSetup: require.resolve('./global-setup'),
  timeout: 30_000,
  use: {
    baseURL: BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});

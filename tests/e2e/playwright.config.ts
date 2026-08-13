import { existsSync } from 'node:fs';
import path from 'node:path';
import { defineConfig, devices } from '@playwright/test';

// Load tests/e2e/.env (gitignored) before `./constants` reads process.env,
// so local runs can override WP_ADMIN_USER/WP_ADMIN_PASSWORD/etc. without
// exporting them in the shell. CI sets these directly in the environment
// and doesn't check in a .env file, so this is a no-op there.
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
  workers: process.env.CI ? 1 : undefined,
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

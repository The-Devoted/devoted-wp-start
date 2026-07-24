import { expect, test as setup } from '@playwright/test';
import { AdminBar } from '../components/AdminBar';
import { STORAGE_STATE } from '../constants';
import { loginAsAdmin } from '../fixtures/auth';

/**
 * Runs once before the `chromium` project (see the `setup` project's
 * `dependencies` in playwright.config.ts). Logging in here — instead of in
 * every spec — both verifies admin login works and produces a storageState
 * file the rest of the suite reuses, so specs skip repeating the login flow.
 */
setup('authenticate as admin', async ({ page }) => {
  await loginAsAdmin(page);
  await expect(new AdminBar(page).howdyMenuItem).toBeVisible();

  await page.context().storageState({ path: STORAGE_STATE });
});

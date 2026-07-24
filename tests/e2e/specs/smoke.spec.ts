import { expect, test } from '@playwright/test';
import { DashboardPage } from '../pages/DashboardPage';
import { FrontEndPage } from '../pages/FrontEndPage';

test.describe('anonymous visitor', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('front page renders without a fatal error', async ({ page }) => {
    const frontEnd = new FrontEndPage(page);
    const response = await frontEnd.goto('/');

    expect(response?.status(), 'front page should not error').toBeLessThan(400);
    await expect(frontEnd.body).not.toContainText('Fatal error');
    await expect(page).toHaveTitle(/.+/);
  });
});

test.describe('authenticated admin', () => {
  test('admin login succeeded and the dashboard loads', async ({ page }) => {
    const dashboard = new DashboardPage(page);
    await dashboard.goto();

    await expect(page).toHaveURL(/\/wp-admin\/?$/);
    await expect(dashboard.adminBar.howdyMenuItem).toBeVisible();
    await expect(dashboard.heading).toHaveText('Dashboard');
  });
});

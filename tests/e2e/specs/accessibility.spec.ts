import { test } from '@playwright/test';
import { expectNoAccessibilityViolations } from '../fixtures/accessibility';
import { DashboardPage } from '../pages/DashboardPage';
import { FrontEndPage } from '../pages/FrontEndPage';
import { LoginPage } from '../pages/LoginPage';

test.describe('anonymous visitor', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('front page has no automatically detectable accessibility violations', async ({ page }) => {
    const frontEnd = new FrontEndPage(page);
    await frontEnd.goto('/');

    await expectNoAccessibilityViolations(page);
  });

  test('login page has no automatically detectable accessibility violations', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();

    await expectNoAccessibilityViolations(page);
  });
});

test.describe('authenticated admin', () => {
  test('dashboard has no automatically detectable accessibility violations', async ({ page }) => {
    const dashboard = new DashboardPage(page);
    await dashboard.goto();

    // The Accessibility Checker plugin's own dashboard widget renders a
    // progressbar axe flags (missing accessible name, invalid
    // aria-valuenow) — a third-party plugin issue, not this repo's to fix.
    await expectNoAccessibilityViolations(page, ['#edac_dashboard_scan_summary']);
  });
});

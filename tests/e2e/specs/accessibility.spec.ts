import { expect, test } from '@playwright/test';
import {
  expectNoAccessibilityViolations,
  getAccessibilityViolations,
  summarizeAccessibilityViolations,
} from '../fixtures/accessibility';
import { fetchSitemapUrls } from '../fixtures/sitemap';
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

test.describe('sitemap pages', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('every page linked from the sitemap has no automatically detectable accessibility violations', async ({
    page,
  }) => {
    const urls = (await fetchSitemapUrls()).filter((url) => !url.includes('/website-user-guide/'));
    test.skip(urls.length === 0, 'Sitemap returned no page URLs to check');

    // Scanning N pages takes longer than a single-page test — scale the
    // timeout with the sitemap size instead of a fixed budget that starts
    // flaking as the site grows content.
    test.setTimeout(urls.length * 10_000 + 30_000);

    const violationSummaries = new Map<string, string>();

    for (const url of urls) {
      await test.step(url, async () => {
        await page.goto(url);
        const violations = await getAccessibilityViolations(page);
        if (violations.length > 0) {
          violationSummaries.set(url, summarizeAccessibilityViolations(violations));
        }
      });
    }

    const report = [...violationSummaries.entries()].map(([url, summary]) => `${url}\n${summary}`).join('\n\n---\n\n');

    expect(violationSummaries.size, report).toBe(0);
  });
});

test.describe('authenticated admin', () => {
  test('dashboard has no automatically detectable accessibility violations', async ({ page }) => {
    const dashboard = new DashboardPage(page);
    await dashboard.goto();

    // The Accessibility Checker plugin's own dashboard widget renders a
    // progressbar axe flags (missing accessible name, invalid
    // aria-valuenow) — a third-party plugin issue, not this repo's to fix.
    // "Hide and do not show again" button fails color-contrast.
    await expectNoAccessibilityViolations(page, [
      '#edac_dashboard_scan_summary',
      '[data-notice="webp-converter-for-media"]',
    ]);
  });
});

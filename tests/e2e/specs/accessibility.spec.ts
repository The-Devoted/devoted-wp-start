import { expect, test } from '@playwright/test';
import {
  expectNoAccessibilityViolations,
  getAccessibilityViolations,
  summarizeAccessibilityViolations,
} from '../fixtures/accessibility';
import { getPrimaryNavUrls } from '../fixtures/navigation';
import { getStructuralScanUrls } from '../fixtures/structuralPages';
import { FrontEndPage } from '../pages/FrontEndPage';

test.describe('anonymous visitor', () => {
  test('front page has no automatically detectable accessibility violations', async ({ page }) => {
    const frontEnd = new FrontEndPage(page);
    await frontEnd.goto('/');

    await expectNoAccessibilityViolations(page);
  });
});

test.describe('site chrome and key templates', () => {
  test('primary nav pages and key templates have no automatically detectable accessibility violations', async ({
    page,
  }) => {
    // Deliberately not a sitemap crawl: this suite's job is to catch
    // regressions in the theme's own chrome and templates, not to
    // continuously re-validate editorial content — the accessibility-checker
    // plugin already does that from inside wp-admin as content is published.
    const navUrls = await getPrimaryNavUrls(page);
    expect(navUrls.length, 'Found no links in the primary nav — check the header.site-header nav selector in fixtures/navigation.ts still matches the theme markup').toBeGreaterThan(0);

    const urls = [...new Set([...navUrls, ...getStructuralScanUrls()])];

    // Scanning N pages takes longer than a single-page test — scale the
    // timeout with the list size instead of a fixed budget.
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

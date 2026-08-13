import AxeBuilder from '@axe-core/playwright';
import { expect, type Page } from '@playwright/test';
import type { Result } from 'axe-core';

const WCAG_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'];

/**
 * Runs an axe-core scan against the current page and returns any WCAG
 * 2.0/2.1 A/AA violations found, without asserting — used where a caller
 * needs to keep going across several pages (e.g. a sitemap crawl) and
 * report everything at the end rather than fail on the first bad page.
 *
 * `exclude` takes CSS selectors for regions to skip — for third-party
 * plugin markup this suite doesn't own (see README "Known gap" notes)
 * rather than the theme/core chrome this suite is meant to catch
 * regressions in.
 */
export async function getAccessibilityViolations(page: Page, exclude: string[] = []): Promise<Result[]> {
  const builder = new AxeBuilder({ page }).withTags(WCAG_TAGS);
  for (const selector of exclude) {
    builder.exclude(selector);
  }
  const results = await builder.analyze();
  return results.violations;
}

/** Readable summary (rule, impact, affected node count, help link) of axe-core violations. */
export function summarizeAccessibilityViolations(violations: Result[]): string {
  return violations
    .map(
      (violation) =>
        `${violation.id} (${violation.impact}): ${violation.help} — ${violation.nodes.length} node(s)\n${violation.helpUrl}`,
    )
    .join('\n\n');
}

/**
 * Runs an axe-core scan against the current page and fails the test with a
 * readable summary if any WCAG 2.0/2.1 A/AA violations are found.
 */
export async function expectNoAccessibilityViolations(page: Page, exclude: string[] = []): Promise<void> {
  const violations = await getAccessibilityViolations(page, exclude);
  expect(violations, summarizeAccessibilityViolations(violations)).toEqual([]);
}

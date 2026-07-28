import AxeBuilder from '@axe-core/playwright';
import { expect, type Page } from '@playwright/test';

/**
 * Runs an axe-core scan against the current page and fails the test with a
 * readable summary (rule, impact, affected node count, help link) if any
 * WCAG 2.0/2.1 A/AA violations are found.
 *
 * `exclude` takes CSS selectors for regions to skip — for third-party
 * plugin markup this suite doesn't own (see README "Known gap" notes)
 * rather than the theme/core chrome this suite is meant to catch
 * regressions in.
 */
export async function expectNoAccessibilityViolations(page: Page, exclude: string[] = []): Promise<void> {
  const builder = new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']);
  for (const selector of exclude) {
    builder.exclude(selector);
  }
  const results = await builder.analyze();

  const summary = results.violations
    .map(
      (violation) =>
        `${violation.id} (${violation.impact}): ${violation.help} — ${violation.nodes.length} node(s)\n${violation.helpUrl}`,
    )
    .join('\n\n');

  expect(results.violations, summary).toEqual([]);
}

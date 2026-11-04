import { test } from '@playwright/test';
import { expectNoAccessibilityViolations } from '../fixtures/accessibility';
import { FrontEndPage } from '../pages/FrontEndPage';

test.describe('anonymous visitor', () => {
  test('front page has no automatically detectable accessibility violations', async ({ page }) => {
    const frontEnd = new FrontEndPage(page);
    await frontEnd.goto('/');

    await expectNoAccessibilityViolations(page);
  });
});

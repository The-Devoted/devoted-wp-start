import { expect, test } from '@playwright/test';
import { FrontEndPage } from '../pages/FrontEndPage';

test.describe('anonymous visitor', () => {
  test('front page renders without a fatal error', async ({ page }) => {
    const frontEnd = new FrontEndPage(page);
    const response = await frontEnd.goto('/');

    expect(response?.status(), 'front page should not error').toBeLessThan(400);
    await expect(frontEnd.body).not.toContainText('Fatal error');
    await expect(page).toHaveTitle(/.+/);
  });
});

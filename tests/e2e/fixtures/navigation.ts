import type { Page } from '@playwright/test';
import { BASE_URL } from '../constants';

/**
 * Internal links inside the site's primary navigation, read from the
 * rendered home page rather than derived from WordPress's nav-menu data
 * model. The primary nav is identified structurally — the `nav` rendered
 * inside the theme's `header` template part — rather than by its label, so
 * this keeps working however a project names or restructures its menu.
 */
export async function getPrimaryNavUrls(page: Page): Promise<string[]> {
  await page.goto('/');
  const hrefs = await page
    .locator('header.site-header nav a[href]')
    .evaluateAll((anchors) => anchors.map((a) => (a as HTMLAnchorElement).href));
  return dedupeInternalUrls(hrefs);
}

function dedupeInternalUrls(urls: string[]): string[] {
  const origin = new URL(BASE_URL).origin;
  return [...new Set(urls)].filter((url) => url.startsWith(origin));
}

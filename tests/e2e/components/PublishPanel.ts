import type { Locator, Page } from '@playwright/test';

/**
 * The "Editor publish" panel Gutenberg opens after the toolbar's Publish
 * button is clicked, where the user confirms and then gets a post-publish
 * confirmation (including the "View Page" link).
 */
export class PublishPanel {
  readonly region: Locator;
  readonly publishButton: Locator;
  readonly viewPageLink: Locator;

  constructor(page: Page) {
    this.region = page.getByRole('region', { name: 'Editor publish' });
    this.publishButton = this.region.getByRole('button', { name: 'Publish', exact: true });
    this.viewPageLink = page.getByRole('link', { name: 'View Page (opens in a new tab)' });
  }
}

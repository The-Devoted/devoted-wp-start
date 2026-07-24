import type { Locator, Page } from '@playwright/test';

/**
 * The public-facing, rendered site — what an anonymous visitor sees, and
 * also what the block editor's "View Page" popup opens. As opposed to
 * wp-admin, which has its own page objects.
 */
export class FrontEndPage {
  readonly body: Locator;

  constructor(private readonly page: Page) {
    this.body = page.locator('body');
  }

  async goto(path = '/') {
    return this.page.goto(path);
  }

  getByText(text: string | RegExp): Locator {
    return this.page.getByText(text);
  }
}

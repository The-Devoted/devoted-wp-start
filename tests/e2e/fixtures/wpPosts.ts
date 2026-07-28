import { test as base } from '@playwright/test';
import { wp } from './wpCli';

type WpPostsFixture = {
  /**
   * Registers a post/page ID (e.g. one created through the block editor UI)
   * for deletion once the test finishes, pass or fail.
   */
  trackPost(postId: number): void;
};

/**
 * Extends the base test with automatic teardown of any WordPress post/page
 * a test creates, so specs stop leaving behind content that piles up in a
 * long-lived instance (and gets re-scanned by things like the sitemap a11y
 * crawl). Following https://playwright.dev/docs/test-fixtures: the fixture
 * hands the test a way to register created assets, then cleans them up
 * itself after the test body runs — specs don't need their own
 * try/finally.
 */
export const test = base.extend<WpPostsFixture>({
  trackPost: async ({}, use) => {
    const postIds: number[] = [];

    await use((postId: number) => {
      postIds.push(postId);
    });

    for (const postId of postIds) {
      // `--force` skips trash — test posts have no reason to linger there.
      wp(['post', 'delete', String(postId), '--force']);
    }
  },
});

export { expect } from '@playwright/test';

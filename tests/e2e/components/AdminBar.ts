import type { Locator, Page } from '@playwright/test';

/**
 * WordPress's persistent top toolbar, shown to every logged-in user across
 * both wp-admin and the public-facing site.
 */
export class AdminBar {
  /**
   * The bar's `navigation` landmark (accessible name "Toolbar"). Useful for
   * scoping queries to admin-bar content, but its own box is CSS-collapsed
   * to zero height (WP's markup relies on floated children) — don't assert
   * `toBeVisible()` on it directly; use `howdyMenuItem` for that.
   */
  readonly root: Locator;

  /** WP's own "Howdy, {user}" greeting — the visible, user-facing signal that a session is authenticated. */
  readonly howdyMenuItem: Locator;

  constructor(page: Page) {
    this.root = page.getByRole('navigation', { name: 'Toolbar' });
    this.howdyMenuItem = page.getByRole('menuitem', { name: /^Howdy,/ });
  }
}

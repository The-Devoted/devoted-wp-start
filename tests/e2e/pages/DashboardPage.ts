import type { Locator, Page } from '@playwright/test';
import { AdminBar } from '../components/AdminBar';

/** The wp-admin Dashboard (`/wp-admin/`), the landing screen after login. */
export class DashboardPage {
  readonly adminBar: AdminBar;
  readonly heading: Locator;

  constructor(private readonly page: Page) {
    this.adminBar = new AdminBar(page);
    this.heading = page.getByRole('heading', { level: 1 }).first();
  }

  async goto(): Promise<void> {
    await this.page.goto('/wp-admin/');
  }
}

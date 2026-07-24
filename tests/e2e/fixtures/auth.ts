import type { Page } from '@playwright/test';
import { ADMIN_PASSWORD, ADMIN_USER } from '../constants';
import { LoginPage } from '../pages/LoginPage';

/** Logs in through the standard wp-login.php form as the test admin user. */
export async function loginAsAdmin(page: Page): Promise<void> {
  const loginPage = new LoginPage(page);
  await loginPage.goto();
  await loginPage.loginAs(ADMIN_USER, ADMIN_PASSWORD);
}

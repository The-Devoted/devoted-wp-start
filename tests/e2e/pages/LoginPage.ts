import type { Locator, Page } from '@playwright/test';
import { wp } from '../fixtures/wpCli';

/**
 * The login URL is invariant for the life of a test run, so this is resolved
 * once and cached rather than shelling out to `wp eval` on every `goto()`.
 */
let loginUrl: string | undefined;
function getLoginUrl(): string {
  return (loginUrl ??= wp(['eval', 'echo wp_login_url();']).trim());
}

/**
 * WordPress's login screen. A login-security plugin (melapress-login-security)
 * can move this off the default `/wp-login.php` via its custom login URL
 * setting, so the URL is read from WordPress itself (`wp_login_url()`)
 * rather than assumed, and stays correct whatever that setting is.
 */
export class LoginPage {
  readonly usernameField: Locator;
  readonly passwordField: Locator;
  readonly submitButton: Locator;

  constructor(private readonly page: Page) {
    this.usernameField = page.getByLabel('Username or Email Address');
    // getByLabel('Password') would also match the "Show password" toggle
    // button (its accessible name contains "password"); role + name scopes
    // this to the textbox itself.
    this.passwordField = page.getByRole('textbox', { name: 'Password' });
    this.submitButton = page.getByRole('button', { name: 'Log In' });
  }

  async goto(): Promise<void> {
    await this.page.goto(getLoginUrl());
  }

  async loginAs(username: string, password: string): Promise<void> {
    await this.usernameField.fill(username);
    await this.passwordField.fill(password);
    await this.submitButton.click();
    await this.page.waitForURL('**/wp-admin/**');
  }
}

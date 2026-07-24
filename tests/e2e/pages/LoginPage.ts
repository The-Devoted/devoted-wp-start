import type { Locator, Page } from '@playwright/test';

/** The standard WordPress login screen at `wp-login.php`. */
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
    await this.page.goto('/wp-login.php');
  }

  async loginAs(username: string, password: string): Promise<void> {
    await this.usernameField.fill(username);
    await this.passwordField.fill(password);
    await this.submitButton.click();
    await this.page.waitForURL('**/wp-admin/**');
  }
}

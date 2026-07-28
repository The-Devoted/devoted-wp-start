import { expect, type FrameLocator, type Locator, type Page } from '@playwright/test';
import { PublishPanel } from '../components/PublishPanel';
import { FrontEndPage } from './FrontEndPage';

type PostType = 'page' | 'post';

/**
 * Gutenberg's block editor (`post-new.php`). Locator and method names mirror
 * Gutenberg's own accessible names ("Add title", "Add default block", the
 * slash inserter) so the suite's vocabulary matches what a reader would see
 * in the UI, rather than inventing parallel terminology.
 */
export class BlockEditorPage {
  /**
   * The editor renders its content (title + blocks) inside an iframe,
   * titled "Editor canvas" — that `title` attribute is what supplies the
   * iframe's accessible name, unlike its `name` attribute (a JS-targeting
   * detail, not an accessibility one).
   */
  readonly canvas: FrameLocator;
  readonly titleField: Locator;
  readonly addDefaultBlockButton: Locator;
  readonly publishButton: Locator;
  readonly publishPanel: PublishPanel;

  constructor(private readonly page: Page) {
    this.canvas = page.frameLocator('iframe[title="Editor canvas"]');
    this.titleField = this.canvas.getByRole('textbox', { name: 'Add title' });
    this.addDefaultBlockButton = this.canvas.getByRole('button', { name: 'Add default block' });
    this.publishButton = page.getByRole('button', { name: 'Publish', exact: true });
    this.publishPanel = new PublishPanel(page);
  }

  async goto(postType: PostType = 'page'): Promise<void> {
    await this.page.goto(`/wp-admin/post-new.php?post_type=${postType}`);
    await this.dismissWelcomeGuide();
  }

  /** Dismisses the "Welcome to the block editor" tips dialog if it appears. */
  async dismissWelcomeGuide(): Promise<void> {
    const closeButton = this.page.getByRole('button', { name: 'Close' });
    if (await closeButton.isVisible().catch(() => false)) {
      await closeButton.click();
    }
  }

  async setTitle(title: string): Promise<void> {
    await this.titleField.fill(title);
  }

  async addParagraph(text: string): Promise<void> {
    await this.addDefaultBlockButton.click();
    await this.page.keyboard.type(text);
  }

  /**
   * Inserts a block by name via the slash inserter — more reliable under
   * Playwright than the sidebar Block Library, which doesn't reliably hand
   * focus to the freshly inserted block. Leaves focus inside the new block,
   * ready for `typeInBlock`.
   */
  async insertBlockViaSlashInserter(blockName: string): Promise<void> {
    await this.page.keyboard.press('Enter');
    await this.page.keyboard.type(`/${blockName}`);
    await expect(this.canvas.getByRole('option', { name: blockName })).toHaveAttribute('aria-selected', 'true');
    await this.page.keyboard.press('Enter');
  }

  async typeInBlock(text: string): Promise<void> {
    await this.page.keyboard.type(text);
  }

  async publish(): Promise<void> {
    await this.publishButton.click();
    await this.publishPanel.publishButton.click();
    await expect(this.page.getByText('is now live.')).toBeVisible({ timeout: 15_000 });
  }

  /**
   * The post/page ID, read back from the `post` query param Gutenberg
   * rewrites the URL to (`post-new.php` → `post.php?post=<id>`) once
   * publishing completes. Used by specs to hand the ID to the `wpPosts`
   * fixture for teardown.
   */
  postId(): number {
    const id = new URL(this.page.url()).searchParams.get('post');
    if (!id) {
      throw new Error(`Could not read post ID from editor URL: ${this.page.url()}`);
    }
    return Number(id);
  }

  /** Opens the published post/page in a new tab via the post-publish panel's "View Page" link. */
  async viewPublishedPage(): Promise<FrontEndPage> {
    const [popup] = await Promise.all([this.page.waitForEvent('popup'), this.publishPanel.viewPageLink.click()]);
    await popup.waitForLoadState();
    return new FrontEndPage(popup);
  }
}

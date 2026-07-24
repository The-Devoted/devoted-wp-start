import { expect, test } from '@playwright/test';
import { BlockEditorPage } from '../pages/BlockEditorPage';

test('editor: create a page with a core block and the custom example block', async ({ page }) => {
  const pageTitle = `E2E Test Page ${Date.now()}`;
  const paragraphText = 'This paragraph was written by the Playwright e2e suite.';
  const exampleMessage = 'Example block message from the e2e suite';

  const editor = new BlockEditorPage(page);
  await editor.goto('page');

  await editor.setTitle(pageTitle);

  // Add a core Paragraph block.
  await editor.addParagraph(paragraphText);

  // Add the theme's custom block.
  await editor.insertBlockViaSlashInserter('Example (TypeScript)');
  await editor.typeInBlock(exampleMessage);

  await editor.publish();
  const publishedPage = await editor.viewPublishedPage();

  await expect(publishedPage.getByText(paragraphText)).toBeVisible();
  await expect(publishedPage.getByText(`${exampleMessage} lmao`)).toBeVisible();
});

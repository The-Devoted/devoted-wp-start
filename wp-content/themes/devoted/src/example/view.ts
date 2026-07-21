/**
 * Frontend-only script. Runs on the published page, not in the editor.
 * Wired up via the `viewScript` key in block.json.
 *
 * Use this for interactivity (event listeners, fetches, animations) that
 * the editor canvas doesn't need. Anything editor-only belongs in edit.tsx
 * instead.
 */
document.querySelectorAll<HTMLElement>('.wp-block-devoted-example').forEach((block) => {
	block.addEventListener('click', () => {
		block.classList.toggle('is-active');
	});
});

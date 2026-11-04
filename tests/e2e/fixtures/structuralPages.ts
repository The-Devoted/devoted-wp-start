import { BASE_URL } from '../constants';
import { wp } from './wpCli';

/**
 * Extra fixed paths to scan that can't be discovered live — e.g. a custom
 * post type's archive, or a template only reachable by a specific URL not
 * linked from primary nav. Add project-specific paths here as needed
 * (leading slash, relative to BASE_URL).
 */
const MANUAL_SCAN_PATHS: string[] = [];

/**
 * A handful of fixed URLs that exercise templates the primary nav won't
 * reach: a single post, one archive per publicly queryable taxonomy
 * (category, tag, and any custom taxonomy a project registers — each can
 * have its own template), a search results page, a 404, and whatever's in
 * MANUAL_SCAN_PATHS above. Resolved live via wp-cli rather than hardcoded
 * slugs, so they stay valid as a project's actual content changes. Any
 * that can't be resolved (e.g. a taxonomy with no terms in use yet) are
 * simply left out, since there's no page to exercise that template with.
 */
export function getStructuralScanUrls(): string[] {
  const urls: string[] = [];

  const postUrl = singleField(['post', 'list', '--post_type=post', '--post_status=publish', '--posts_per_page=1', '--orderby=date', '--order=desc', '--field=url']);
  if (postUrl) {
    urls.push(postUrl);

    const title = singleField(['post', 'list', '--post_type=post', '--post_status=publish', '--posts_per_page=1', '--orderby=date', '--order=desc', '--field=title']);
    const searchTerm = title?.split(/\s+/)[0];
    if (searchTerm) urls.push(`${BASE_URL}/?s=${encodeURIComponent(searchTerm)}`);
  }

  for (const taxonomy of publiclyQueryableTaxonomies()) {
    const termUrl = singleField(['term', 'list', taxonomy, '--number=1', '--hide_empty=1', '--field=url']);
    if (termUrl) urls.push(termUrl);
  }

  urls.push(...MANUAL_SCAN_PATHS.map((path) => `${BASE_URL}${path}`));
  urls.push(`${BASE_URL}/e2e-accessibility-scan-404-check/`);

  return urls;
}

/**
 * `wp taxonomy list` doesn't support filtering by `publicly_queryable`
 * (it's resolved per-taxonomy, not a queryable field), so this fetches the
 * field for every taxonomy and filters client-side instead.
 */
function publiclyQueryableTaxonomies(): string[] {
  const [, ...rows] = wp(['taxonomy', 'list', '--fields=name,publicly_queryable']).trim().split('\n');
  return rows
    .map((row) => row.split('\t'))
    .filter(([, publiclyQueryable]) => publiclyQueryable === '1')
    .map(([name]) => name);
}

function singleField(args: string[]): string | undefined {
  return wp(args).trim() || undefined;
}

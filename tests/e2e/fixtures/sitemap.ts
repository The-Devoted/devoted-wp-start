import { BASE_URL } from '../constants';

const LOC_PATTERN = /<loc>(.*?)<\/loc>/g;

/**
 * Walks a sitemap index and returns every page URL it lists. WordPress (via
 * Yoast SEO here) publishes one sub-sitemap per post type/taxonomy rather
 * than listing pages directly, so entries ending in `.xml` are treated as
 * nested sitemaps to expand; everything else is a page URL.
 */
export async function fetchSitemapUrls(indexUrl = `${BASE_URL}/sitemap_index.xml`): Promise<string[]> {
  return fetchLocs(indexUrl, new Set());
}

async function fetchLocs(url: string, visited: Set<string>): Promise<string[]> {
  if (visited.has(url)) return [];
  visited.add(url);

  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`Failed to fetch sitemap ${url}: ${response.status} ${response.statusText}`);
  }
  const xml = await response.text();
  const locs = [...xml.matchAll(LOC_PATTERN)].map((match) => decodeXmlEntities(match[1]));

  const sitemaps = locs.filter((loc) => loc.endsWith('.xml'));
  const pages = locs.filter((loc) => !loc.endsWith('.xml'));
  const nested = await Promise.all(sitemaps.map((loc) => fetchLocs(loc, visited)));

  return [...pages, ...nested.flat()];
}

function decodeXmlEntities(value: string): string {
  return value
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&apos;/g, "'");
}

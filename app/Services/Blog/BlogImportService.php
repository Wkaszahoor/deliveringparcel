<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use App\Models\Blog\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogImportLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Blog Import System (2026-09-02) — WordPress → new-design blog.
 *
 * Parses WXR / atom / RSS / sitemap XML in one class, fetches post
 * content from URLs when the feed has none, downloads remote images to
 * public/blog/{batch}/…, and stores everything via BlogPost.
 *
 * OPTIONS (passed from Admin\BlogImportController::upload):
 *   - default_status: 'draft'|'published' — applied to entries whose
 *     status comes from the parser's generic 'published' default
 *     (atom/RSS/sitemap/fetched). WXR keeps its own resolved status
 *     (publish→published, anything else→draft).
 *   - overwrite:      re-import entries whose source_guid/url exists.
 *   - fetch_content:  crawl each URL that has no inline content.
 */
class BlogImportService
{
    private string $batchId;
    private BlogImportLog $importLog;
    private array $errors = [];
    private int $imported = 0;
    private int $skipped  = 0;
    private int $failed   = 0;

    /* ── ENTRY POINT ────────────────────────────────────────────── */

    public function importFromFile(
        string $filePath,
        string $originalFilename,
        int $userId,
        array $options = []
    ): BlogImportLog {
        $this->batchId = Str::uuid()->toString();

        $this->importLog = BlogImportLog::create([
            'batch_id'    => $this->batchId,
            'filename'    => $originalFilename,
            'file_type'   => $this->detectFileType($filePath, $originalFilename),
            'status'      => 'processing',
            'started_at'  => now(),
            'imported_by' => $userId,
        ]);

        try {
            $content = file_get_contents($filePath);
            $entries = $this->parse($content, $this->importLog->file_type);

            $this->importLog->update(['total_found' => count($entries)]);

            foreach ($entries as $entry) {
                $this->processEntry($entry, $options);
            }

            $this->importLog->update([
                'status'         => $this->failed > 0 && $this->imported > 0
                                      ? 'partial'
                                      : ($this->failed > 0 ? 'failed' : 'completed'),
                'total_imported' => $this->imported,
                'total_skipped'  => $this->skipped,
                'total_failed'   => $this->failed,
                'error_log'      => $this->errors,
                'completed_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            $this->importLog->update([
                'status'    => 'failed',
                'error_log' => [['error' => $e->getMessage()]],
                'completed_at' => now(),
            ]);
            Log::error('BlogImport failed: ' . $e->getMessage());
        }

        return $this->importLog->fresh();
    }

    /* ── FILE TYPE DETECTION ────────────────────────────────────── */

    private function detectFileType(string $path, string $filename): string
    {
        $peek = substr((string) file_get_contents($path, false, null, 0, 2000), 0, 2000);

        if (str_contains($peek, 'xmlns:wp=') || str_contains($peek, 'wp:post_type'))
            return 'wxr';
        if (str_contains($peek, '<feed') && str_contains($peek, 'xmlns'))
            return 'atom';
        if (str_contains($peek, '<rss') || str_contains($peek, '<channel'))
            return 'rss';
        return 'sitemap_xml';
    }

    /* ── PARSE DISPATCHER ───────────────────────────────────────── */

    private function parse(string $xml, string $type): array
    {
        return match ($type) {
            'wxr'         => $this->parseWXR($xml),
            'atom'        => $this->parseAtom($xml),
            'rss'         => $this->parseRSS($xml),
            'sitemap_xml' => $this->parseSitemap($xml),
            default       => [],
        };
    }

    /* ── WXR PARSER (WordPress eXtended RSS — most complete) ─────── */

    private function parseWXR(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);

        // Register ALL namespaces before any xpath query — a missing
        // namespace means a silently empty result.
        $xpath->registerNamespace('wp',      'http://wordpress.org/export/1.2/');
        $xpath->registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
        $xpath->registerNamespace('dc',      'http://purl.org/dc/elements/1.1/');
        $xpath->registerNamespace('excerpt', 'http://wordpress.org/export/1.2/excerpt/');

        $items = $xpath->query('//channel/item');
        $entries = [];

        foreach ($items as $item) {
            $postType = $this->xpathText($xpath, 'wp:post_type', $item);
            if ($postType !== 'post') continue; // skip pages, attachments

            $status = $this->xpathText($xpath, 'wp:status', $item);
            if (in_array($status, ['trash', 'auto-draft'])) continue;

            $categories = [];
            $tags       = [];
            foreach ($xpath->query('category', $item) as $cat) {
                $domain = $cat->getAttribute('domain');
                $value  = trim($cat->textContent);
                if ($domain === 'category') $categories[] = $value;
                if ($domain === 'post_tag') $tags[] = $value;
            }

            $content = $this->xpathText($xpath, 'content:encoded', $item);
            // Convert WordPress blocks / shortcodes to clean HTML
            $content = $this->cleanWordPressContent($content);

            $entries[] = [
                'title'        => $this->xpathText($xpath, 'title', $item),
                'content'      => $content,
                'excerpt'      => $this->cleanWordPressContent(
                                    $this->xpathText($xpath, 'excerpt:encoded', $item)
                                  ),
                'source_url'   => $this->xpathText($xpath, 'link', $item),
                'source_guid'  => $this->xpathText($xpath, 'guid', $item),
                'author'       => $this->xpathText($xpath, 'dc:creator', $item),
                'published_at' => $this->xpathText($xpath, 'pubDate', $item),
                // WXR status is EXPLICIT — default_status does not override it.
                'status'       => $status === 'publish' ? 'published' : 'draft',
                '_explicit_status' => true,
                'categories'   => $categories,
                'tags'         => $tags,
                'featured_image_url' => $this->extractFeaturedImage($xpath, $item, $doc),
            ];
        }

        return $entries;
    }

    /* ── ATOM PARSER (sitemap.atom) ─────────────────────────────── */

    private function parseAtom(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');

        // Try with and without namespace prefix
        $entries_ns  = $xpath->query('//atom:entry');
        $entries_raw = $xpath->query('//entry');
        $nodes = $entries_ns->length > 0 ? $entries_ns : $entries_raw;

        $entries = [];
        foreach ($nodes as $entry) {
            // Get the post URL — atom feed may only have the link
            $link = '';
            foreach ($xpath->query('atom:link|link', $entry) as $l) {
                $rel  = $l->getAttribute('rel');
                $href = $l->getAttribute('href');
                if ($rel === 'alternate' || empty($rel)) { $link = $href; break; }
            }

            $content = '';
            $contentNode = $xpath->query('atom:content|content', $entry)->item(0);
            if ($contentNode) $content = $contentNode->textContent;

            $summaryNode = $xpath->query('atom:summary|summary', $entry)->item(0);
            $excerpt = $summaryNode ? $summaryNode->textContent : '';

            $titleNode = $xpath->query('atom:title|title', $entry)->item(0);
            $title = $titleNode ? $titleNode->textContent : '';

            $pubNode = $xpath->query('atom:published|published|atom:updated|updated', $entry)->item(0);
            $pubDate = $pubNode ? $pubNode->textContent : null;

            $authorNode = $xpath->query('atom:author/atom:name|author/name', $entry)->item(0);
            $author = $authorNode ? $authorNode->textContent : '';

            // If atom feed is a SITEMAP (no content), schedule a fetch
            $needsFetch = empty(trim($content)) && !empty($link);

            $entries[] = [
                'title'        => $title,
                'content'      => $content,
                'excerpt'      => $excerpt,
                'source_url'   => $link,
                'source_guid'  => $link,
                'author'       => $author,
                'published_at' => $pubDate,
                'status'       => 'published', // generic default — default_status applies
                'categories'   => [],
                'tags'         => [],
                'featured_image_url' => null,
                '_needs_fetch' => $needsFetch, // flag to fetch full content
            ];
        }

        return $entries;
    }

    /* ── RSS PARSER ─────────────────────────────────────────────── */

    private function parseRSS(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
        $xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');

        $items = $xpath->query('//channel/item');
        $entries = [];

        foreach ($items as $item) {
            $content = $this->xpathText($xpath, 'content:encoded', $item);
            if (empty($content)) {
                $content = $this->xpathText($xpath, 'description', $item);
            }

            $cats = [];
            foreach ($xpath->query('category', $item) as $c) {
                $cats[] = trim($c->textContent);
            }

            $entries[] = [
                'title'        => $this->xpathText($xpath, 'title', $item),
                'content'      => $this->cleanWordPressContent($content),
                'excerpt'      => '',
                'source_url'   => $this->xpathText($xpath, 'link', $item),
                'source_guid'  => $this->xpathText($xpath, 'guid', $item),
                'author'       => $this->xpathText($xpath, 'dc:creator', $item),
                'published_at' => $this->xpathText($xpath, 'pubDate', $item),
                'status'       => 'published', // generic default — default_status applies
                'categories'   => $cats,
                'tags'         => [],
                'featured_image_url' => null,
                '_needs_fetch' => empty(trim($content)),
            ];
        }

        return $entries;
    }

    /* ── SITEMAP XML PARSER (URLs only — fetches each page) ─────── */

    private function parseSitemap(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('sm', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $locs = $xpath->query('//sm:url/sm:loc|//url/loc');
        $entries = [];

        foreach ($locs as $loc) {
            $url = trim($loc->textContent);
            if (empty($url)) continue;
            // Skip non-post URLs (tag, category, author, page pages)
            if (preg_match('#/(tag|category|author|page|feed|wp-content)/#i', $url))
                continue;

            $entries[] = [
                'title'        => '',
                'content'      => '',
                'excerpt'      => '',
                'source_url'   => $url,
                'source_guid'  => $url,
                'author'       => '',
                'published_at' => null,
                'status'       => 'published', // generic default — default_status applies
                'categories'   => [],
                'tags'         => [],
                'featured_image_url' => null,
                '_needs_fetch' => true,
            ];
        }

        return $entries;
    }

    /* ── PROCESS A SINGLE ENTRY ─────────────────────────────────── */

    private function processEntry(array $entry, array $options): void
    {
        try {
            $guid = $entry['source_guid'] ?? null;
            $url  = $entry['source_url'] ?? null;

            // Skip if already imported (by source_guid or source_url).
            // Only dedupe when we actually have an identifier.
            if (($guid !== null && $guid !== '') || ($url !== null && $url !== '')) {
                $exists = BlogPost::withTrashed()
                    ->where(function ($q) use ($guid, $url) {
                        if ($guid !== null && $guid !== '') $q->where('source_guid', $guid);
                        if ($url !== null && $url !== '') $q->orWhere('source_url', $url);
                    })
                    ->exists();

                if ($exists && !($options['overwrite'] ?? false)) {
                    $this->skipped++;
                    return;
                }
            }

            // If content is empty and _needs_fetch, crawl the URL
            if (!empty($entry['_needs_fetch'])
                && !empty($entry['source_url'])
                && ($options['fetch_content'] ?? true)) {
                $fetched = $this->fetchPostFromUrl($entry['source_url']);
                if ($fetched) {
                    $entry = array_merge($entry, $fetched);
                }
            }

            // Must have a title at minimum
            if (empty(trim($entry['title'] ?? ''))) {
                $this->skipped++;
                return;
            }

            // Resolve status: WXR keeps its explicit status; parsers that
            // emit the generic 'published' default honour default_status.
            $status = $entry['status'] ?? 'published';
            if (empty($entry['_explicit_status']) && $status === 'published') {
                $status = $options['default_status'] ?? 'published';
            }

            // Download featured image
            $localImage = null;
            if (!empty($entry['featured_image_url'])) {
                $localImage = $this->downloadImage(
                    $entry['featured_image_url'],
                    $this->batchId
                );
            }

            // Also download all inline images in content
            if (!empty($entry['content'])) {
                $entry['content'] = $this->localizeContentImages(
                    $entry['content'],
                    $this->batchId
                );
            }

            // Parse published date
            $publishedAt = null;
            if (!empty($entry['published_at'])) {
                try {
                    $publishedAt = Carbon::parse($entry['published_at']);
                } catch (\Throwable $e) {
                    $publishedAt = null;
                }
            }

            $slug = BlogPost::generateSlug($entry['title']);

            $attributes = [
                'title'               => $entry['title'],
                'slug'                => $slug,
                'excerpt'             => $entry['excerpt'] ?? '',
                'content'             => $entry['content'] ?? '',
                'featured_image'      => $localImage,
                'featured_image_url'  => $entry['featured_image_url'] ?? null,
                'author_name'         => $entry['author'] ?? null,
                'status'              => $status,
                'source_url'          => $url,
                'published_at'        => $publishedAt ?? now(),
                'imported_at'         => now(),
                'import_batch_id'     => $this->batchId,
            ];

            // Create or update (match on guid, else url; never on NULLs)
            if ($guid !== null && $guid !== '') {
                $post = BlogPost::withTrashed()->updateOrCreate(
                    ['source_guid' => $guid],
                    $attributes
                );
                if ($post->trashed()) $post->restore();
            } elseif ($url !== null && $url !== '') {
                $post = BlogPost::withTrashed()->updateOrCreate(
                    ['source_url' => $url],
                    $attributes
                );
                if ($post->trashed()) $post->restore();
            } else {
                $post = BlogPost::create($attributes);
            }

            // Attach categories
            if (!empty($entry['categories'])) {
                $catIds = collect($entry['categories'])
                    ->filter()
                    ->map(fn ($c) => BlogCategory::findOrCreateByName($c)->id)
                    ->toArray();
                $post->categories()->sync($catIds);
            }

            // Attach tags
            if (!empty($entry['tags'])) {
                $tagIds = collect($entry['tags'])
                    ->filter()
                    ->map(fn ($t) => BlogTag::findOrCreateByName($t)->id)
                    ->toArray();
                $post->tags()->sync($tagIds);
            }

            $this->imported++;

        } catch (\Throwable $e) {
            $this->failed++;
            $this->errors[] = [
                'url'   => $entry['source_url'] ?? 'unknown',
                'title' => $entry['title'] ?? '',
                'error' => $e->getMessage(),
            ];
            Log::warning('BlogImport entry failed: ' . $e->getMessage());
        }
    }

    /* ── FETCH FULL POST FROM URL (sitemap / atom with no content) ─ */

    private function fetchPostFromUrl(string $url): ?array
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; BlogImporter/1.0)'])
                ->get($url);

            if (!$response->successful()) return null;

            $html = $response->body();
            $doc  = new \DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            libxml_clear_errors();
            $xpath = new \DOMXPath($doc);

            // Extract title
            $title = '';
            foreach ([
                '//h1',
                '//meta[@property="og:title"]/@content',
                '//title',
            ] as $q) {
                $node = $xpath->query($q)->item(0);
                if ($node) {
                    $title = trim($node->textContent ?: $node->nodeValue);
                    if ($title) break;
                }
            }

            // Extract main content — try common WordPress selectors
            $content = '';
            foreach ([
                '//article[contains(@class,"post")]',
                '//*[contains(@class,"entry-content")]',
                '//*[contains(@class,"post-content")]',
                '//*[contains(@class,"article-content")]',
                '//*[@id="content"]',
                '//main',
            ] as $sel) {
                $node = $xpath->query($sel)->item(0);
                if ($node) {
                    $content = $doc->saveHTML($node);
                    break;
                }
            }

            // Featured image from og:image
            $ogImage = null;
            $ogNode  = $xpath->query('//meta[@property="og:image"]/@content')->item(0);
            if ($ogNode) $ogImage = trim($ogNode->nodeValue);

            // Description from og:description or meta description
            $desc = '';
            foreach ([
                '//meta[@property="og:description"]/@content',
                '//meta[@name="description"]/@content',
            ] as $q) {
                $n = $xpath->query($q)->item(0);
                if ($n) { $desc = trim($n->nodeValue); break; }
            }

            // Published date
            $pubDate = null;
            foreach ([
                '//meta[@property="article:published_time"]/@content',
                '//time/@datetime',
                '//meta[@name="date"]/@content',
            ] as $q) {
                $n = $xpath->query($q)->item(0);
                if ($n) { $pubDate = trim($n->nodeValue); break; }
            }

            // Author
            $author = '';
            foreach ([
                '//meta[@name="author"]/@content',
                '//a[contains(@rel,"author")]',
                '//*[contains(@class,"author-name")]',
            ] as $q) {
                $n = $xpath->query($q)->item(0);
                if ($n) { $author = trim($n->textContent ?: $n->nodeValue); break; }
            }

            // Categories / tags from article:tag meta
            $cats = [];
            foreach ($xpath->query('//meta[@property="article:tag"]/@content') as $t) {
                $cats[] = trim($t->nodeValue);
            }

            if (empty($title)) return null;

            return [
                'title'              => $title,
                'content'            => $this->cleanWordPressContent($content),
                'excerpt'            => $desc,
                'author'             => $author,
                'published_at'       => $pubDate,
                'featured_image_url' => $ogImage,
                'categories'         => $cats,
                'tags'               => [],
            ];

        } catch (\Throwable $e) {
            Log::debug('BlogImport fetch failed: ' . $url . ' — ' . $e->getMessage());
            return null;
        }
    }

    /* ── IMAGE DOWNLOADER ───────────────────────────────────────── */

    private function downloadImage(string $url, string $batchId): ?string
    {
        if (empty($url)) return null;
        try {
            $response = Http::timeout(20)->get($url);
            if (!$response->successful()) return null;

            $ext      = $this->guessExtension($url, $response->header('Content-Type'));
            $filename = 'blog/' . $batchId . '/' . Str::random(16) . '.' . $ext;
            $fullPath = public_path($filename);

            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            file_put_contents($fullPath, $response->body());
            return $filename;
        } catch (\Throwable $e) {
            Log::debug('BlogImport image download failed: ' . $url);
            return null;
        }
    }

    /* Replace remote image srcs in HTML content with locally downloaded copies. */
    private function localizeContentImages(string $html, string $batchId): string
    {
        return preg_replace_callback(
            '/src=["\']([^"\']+\.(jpg|jpeg|png|gif|webp)([^"\']*)?)["\']/i',
            function ($m) use ($batchId) {
                $url   = $m[1];
                $local = $this->downloadImage($url, $batchId);
                if ($local) {
                    return 'src="' . asset($local) . '"';
                }
                return $m[0]; // keep original if download failed
            },
            $html
        );
    }

    /* ── WORDPRESS CONTENT CLEANER ──────────────────────────────── */

    private function cleanWordPressContent(string $content): string
    {
        if (empty($content)) return '';

        // Remove Gutenberg block comments
        $content = preg_replace('/<!--\s*wp:[^>]*-->/i', '', $content);
        $content = preg_replace('/<!--\s*\/wp:[^>]*-->/i', '', $content);

        // Remove common shortcodes (non-destructive — keeps inner content)
        $content = preg_replace('/\[caption[^\]]*\](.*?)\[\/caption\]/is', '$1', $content);
        $content = preg_replace('/\[[a-z_-]+[^\]]*\](.*?)\[[\/a-z_-]+\]/is', '$1', $content);
        $content = preg_replace('/\[[a-z_-]+[^\]]*\/?\]/i', '', $content);

        // Fix relative URLs (//example.com → https://example.com)
        $content = preg_replace('#src=["\']//([^"\']+)["\']#', 'src="https://$1"', $content);

        // Strip inline WordPress classes that pollute the markup
        $content = preg_replace('/ class=["\'][^"\']*wp-[^"\']*["\']/i', '', $content);

        return trim($content);
    }

    /* ── HELPERS ────────────────────────────────────────────────── */

    private function xpathText(\DOMXPath $x, string $q, \DOMNode $ctx): string
    {
        $n = $x->query($q, $ctx)->item(0);
        return $n ? trim($n->textContent) : '';
    }

    private function extractFeaturedImage(\DOMXPath $xpath, \DOMNode $item, \DOMDocument $doc): ?string
    {
        // wp:attachment_url via postmeta with _thumbnail_id reference
        foreach ($xpath->query('wp:postmeta', $item) as $meta) {
            $key = $this->xpathText($xpath, 'wp:meta_key', $meta);
            if ($key === '_thumbnail_id') {
                // Find attachment with this ID in the full doc
                $thumbId = $this->xpathText($xpath, 'wp:meta_value', $meta);
                $attUrl  = $xpath->query(
                    "//item[wp:post_id='{$thumbId}']/wp:attachment_url"
                )->item(0);
                if ($attUrl) return trim($attUrl->textContent);
            }
        }
        // Fallback: first <img> in content
        $content = $this->xpathText($xpath, 'content:encoded', $item);
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $m)) {
            return $m[1];
        }
        return null;
    }

    private function guessExtension(string $url, ?string $contentType): string
    {
        $map = [
            'image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/pjpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        if ($contentType) {
            $type = strtolower(trim(explode(';', $contentType)[0]));
            if (isset($map[$type])) return $map[$type];
        }
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i', $url, $m))
            return strtolower($m[1]);
        return 'jpg';
    }
}
